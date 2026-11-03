<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialLot;
use App\Models\ProductCategory;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\StockMovement;
use App\Models\StorageLocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SemiFinishedGoodsController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in(['in_stock', 'low_stock', 'out_of_stock', 'expiring'])],
            'per_page' => ['nullable', Rule::in(['10', '25', '50', '100'])],
        ]);

        $baseScope = function (Builder $query) {
            $query->where('materials.is_active', true)
                ->whereNull('materials.packaging_type')
                ->where('materials.type', 'semi_finished_good');
        };

        $allItems = Material::query()
            ->tap($baseScope)
            ->with([
                'productCategory',
                'category',
                'recipes.material' => fn ($q) => $q->withSum(['lots as usable_stock' => function ($lq) {
                    $lq->where('quantity_on_hand', '>', 0)
                        ->where(fn ($sq) => $sq->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', today()));
                }], 'quantity_on_hand'),
            ])
            ->withSum('lots', 'quantity_on_hand')
            ->orderBy('materials.name')
            ->get();

        $inStockCount = 0;
        $lowStockCount = 0;
        $outOfStockCount = 0;

        foreach ($allItems as $item) {
            $onHand = (float) ($item->lots_sum_quantity_on_hand ?? 0);
            $reorder = (float) ($item->reorder_level ?? 0);
            if ($onHand <= 0) {
                $outOfStockCount++;
            } elseif ($onHand <= $reorder) {
                $lowStockCount++;
            } else {
                $inStockCount++;
            }
        }

        $expiringCount = Material::query()
            ->tap($baseScope)
            ->whereHas('lots', fn (Builder $lots) => $lots
                ->whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [today(), today()->addDays(90)])
                ->where('quantity_on_hand', '>', 0))
            ->count();

        $stats = [
            'total' => $allItems->count(),
            'in_stock' => $inStockCount,
            'low_stock' => $lowStockCount,
            'out_of_stock' => $outOfStockCount,
            'expiring' => $expiringCount,
            'total_stock' => (float) $allItems->sum(fn (Material $p) => (float) ($p->lots_sum_quantity_on_hand ?? 0)),
        ];

        $quantitySql = '(select coalesce(sum(material_lots.quantity_on_hand), 0) from material_lots where material_lots.material_id = materials.id)';

        $productsQuery = Material::query()
            ->tap($baseScope)
            ->with([
                'productCategory',
                'category',
                'recipes.material',
                'lots' => fn ($q) => $q->latest()->limit(1),
                'stockMovements' => fn ($q) => $q->where('movement_type', 'production_output')->latest()->limit(1),
            ])
            ->withSum('lots', 'quantity_on_hand')
            ->addSelect([
                'next_expiry_date' => MaterialLot::query()
                    ->select('expiry_date')
                    ->whereColumn('material_lots.material_id', 'materials.id')
                    ->whereNotNull('expiry_date')
                    ->where('quantity_on_hand', '>', 0)
                    ->orderBy('expiry_date')
                    ->limit(1),
                'effective_storage_location' => MaterialLot::query()
                    ->select('storage_location')
                    ->whereColumn('material_lots.material_id', 'materials.id')
                    ->whereNotNull('storage_location')
                    ->orderByRaw('expiry_date is null')
                    ->orderBy('expiry_date')
                    ->limit(1),
            ]);

        if (! empty($filters['q'])) {
            $term = '%'.trim((string) $filters['q']).'%';
            $productsQuery->where(function (Builder $sq) use ($term) {
                $sq->where('materials.name', 'like', $term)
                    ->orWhere('materials.item_code', 'like', $term)
                    ->orWhere('materials.storage_location', 'like', $term)
                    ->orWhereHas('productCategory', fn (Builder $cq) => $cq->where('name', 'like', $term))
                    ->orWhereHas('category', fn (Builder $cq) => $cq->where('name', 'like', $term));
            });
        }

        if (! empty($filters['category_id'])) {
            $catId = (int) $filters['category_id'];
            $productsQuery->where('materials.product_category_id', $catId);
        }

        match ($filters['status'] ?? null) {
            'in_stock' => $productsQuery->whereRaw($quantitySql.' > materials.reorder_level and '.$quantitySql.' > 0'),
            'low_stock' => $productsQuery->whereRaw($quantitySql.' > 0 and '.$quantitySql.' <= materials.reorder_level'),
            'out_of_stock' => $productsQuery->whereRaw($quantitySql.' <= 0'),
            'expiring' => $productsQuery->whereHas('lots', fn (Builder $lots) => $lots
                ->whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [today(), today()->addDays(90)])
                ->where('quantity_on_hand', '>', 0)),
            default => null,
        };

        $products = $productsQuery
            ->orderBy('materials.name')
            ->orderBy('materials.id')
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();

        $categories = ProductCategory::where('is_active', true)->orderBy('name')->get();
        if ($categories->isEmpty()) {
            $categories = Category::where('is_active', true)->orderBy('name')->get();
        }

        $year = now()->format('Y');
        $countThisYear = ProductionOrder::whereYear('created_at', $year)->count();
        $defaultBatchNo = "PB-{$year}-" . str_pad($countThisYear + 1, 4, '0', STR_PAD_LEFT);
        $storageLocations = StorageLocation::where('is_active', true)->orderBy('name')->get();

        return view('semi-finished-goods.index', [
            'products' => $products,
            'predefinedProducts' => $allItems,
            'categories' => $categories,
            'stats' => $stats,
            'defaultBatchNo' => $defaultBatchNo,
            'storageLocations' => $storageLocations,
        ]);
    }

    public function recordProduction(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', Rule::exists('materials', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'batch_number' => ['nullable', 'string', 'max:50'],
            'produced_at' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'storage_location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'ingredients' => ['nullable', 'array'],
            'ingredients.*.material_id' => ['required_with:ingredients', Rule::exists('materials', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'ingredients.*.quantity' => ['required_with:ingredients', 'numeric', 'gt:0'],
        ]);

        $product = Material::with('recipes.material')->findOrFail($data['product_id']);
        $quantityToProduce = (float) $data['quantity'];
        $producedAt = $data['produced_at'] ?? today()->toDateString();
        $year = now()->parse($producedAt)->format('Y');
        $countThisYear = ProductionOrder::whereYear('created_at', $year)->count();
        $batchNumber = ! empty($data['batch_number'])
            ? trim($data['batch_number'])
            : "PB-{$year}-" . str_pad($countThisYear + 1, 4, '0', STR_PAD_LEFT);

        $orderNumber = $batchNumber;
        if (ProductionOrder::where('order_number', $orderNumber)->exists()) {
            $orderNumber = $batchNumber . '-' . Str::upper(Str::random(4));
        }

        // Determine inputs from submitted ingredients or scale from predefined 1-unit recipe
        $inputs = [];
        if (! empty($data['ingredients'])) {
            foreach ($data['ingredients'] as $row) {
                if (! empty($row['material_id']) && ! empty($row['quantity']) && (float) $row['quantity'] > 0) {
                    $inputs[] = [
                        'material_id' => (int) $row['material_id'],
                        'quantity' => round((float) $row['quantity'], 2),
                    ];
                }
            }
        } elseif ($product->recipes->isNotEmpty()) {
            foreach ($product->recipes as $recipe) {
                if ($recipe->material_id && (float) $recipe->quantity > 0) {
                    $inputs[] = [
                        'material_id' => (int) $recipe->material_id,
                        'quantity' => round((float) $recipe->quantity * $quantityToProduce, 2),
                    ];
                }
            }
        }

        DB::transaction(function () use ($product, $quantityToProduce, $producedAt, $batchNumber, $orderNumber, $data, $inputs, $request) {
            $order = ProductionOrder::create([
                'order_number' => $orderNumber,
                'produced_at' => $producedAt,
                'product_category_id' => $product->product_category_id,
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($inputs as $input) {
                $ingMaterial = Material::query()->findOrFail($input['material_id']);
                $remaining = (float) $input['quantity'];

                $lots = MaterialLot::query()
                    ->where('material_id', $ingMaterial->id)
                    ->where('quantity_on_hand', '>', 0)
                    ->where(fn ($q) => $q->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', $producedAt))
                    ->orderByRaw('expiry_date is null')
                    ->orderBy('expiry_date')
                    ->lockForUpdate()
                    ->get();

                $availableTotal = (float) $lots->sum('quantity_on_hand');
                if ($availableTotal + 0.0001 < $remaining) {
                    throw ValidationException::withMessages([
                        'ingredients' => "Insufficient unexpired stock for {$ingMaterial->name} (Required: " . number_format($remaining, 2) . " {$ingMaterial->stock_unit}, Available: " . number_format($availableTotal, 2) . " {$ingMaterial->stock_unit}).",
                    ]);
                }

                foreach ($lots as $lot) {
                    if ($remaining <= 0) {
                        break;
                    }

                    $used = min($remaining, (float) $lot->quantity_on_hand);
                    $balance = round((float) $lot->quantity_on_hand - $used, 3);
                    $lot->update(['quantity_on_hand' => $balance]);

                    StockMovement::create([
                        'material_id' => $ingMaterial->id,
                        'material_lot_id' => $lot->id,
                        'movement_type' => 'production_consumption',
                        'quantity' => -$used,
                        'balance_after' => $balance,
                        'reference_type' => 'production',
                        'reference_id' => $order->id,
                        'notes' => "Consumed for {$product->name} ({$batchNumber})",
                        'created_by' => $request->user()->id,
                    ]);

                    $remaining = round($remaining - $used, 3);
                }

                ProductionOrderItem::create([
                    'production_order_id' => $order->id,
                    'material_id' => $ingMaterial->id,
                    'direction' => 'input',
                    'quantity' => $input['quantity'],
                ]);
            }

            $finalLotNo = $batchNumber;
            $counter = 1;
            while (MaterialLot::where('material_id', $product->id)->where('lot_number', $finalLotNo)->exists()) {
                $finalLotNo = $batchNumber . '-' . $counter;
                $counter++;
            }

            $outputLot = MaterialLot::create([
                'material_id' => $product->id,
                'lot_number' => $finalLotNo,
                'quantity_received' => $quantityToProduce,
                'quantity_on_hand' => $quantityToProduce,
                'manufactured_date' => $producedAt,
                'expiry_date' => $data['expiry_date'] ?? null,
                'storage_location' => ! empty($data['storage_location']) ? $data['storage_location'] : $product->storage_location,
                'unit_cost' => $product->cost_per_unit,
                'status' => 'active',
                'notes' => ! empty($data['notes']) ? $data['notes'] : "Production batch {$finalLotNo}",
            ]);

            if (! empty($data['storage_location'])) {
                $product->update(['storage_location' => $data['storage_location']]);
            }

            ProductionOrderItem::create([
                'production_order_id' => $order->id,
                'material_id' => $product->id,
                'direction' => 'output',
                'quantity' => $quantityToProduce,
                'unit_cost' => $product->cost_per_unit,
            ]);

            StockMovement::create([
                'material_id' => $product->id,
                'material_lot_id' => $outputLot->id,
                'movement_type' => 'production_output',
                'quantity' => $quantityToProduce,
                'balance_after' => $outputLot->quantity_on_hand,
                'reference_type' => 'production',
                'reference_id' => $order->id,
                'notes' => ! empty($data['notes']) ? $data['notes'] : "Production batch {$finalLotNo}",
                'created_by' => $request->user()->id,
            ]);
        });

        return redirect()->route('semi-finished-goods.index')
            ->with('success_title', 'Production Recorded')
            ->with('success', 'Recorded production of ' . number_format($quantityToProduce, 2) . " {$product->stock_unit} of “{$product->name}” (Batch {$batchNumber}).");
    }
}
