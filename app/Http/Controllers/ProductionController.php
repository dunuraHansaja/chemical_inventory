<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialLot;
use App\Models\ProductCategory;
use App\Models\ProductRecipe;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductionController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $filterCategoryId = $request->input('category_id');

        $prodCats = ProductCategory::where('is_active', true)->orderBy('name')->get();
        $useProductCategories = $prodCats->isNotEmpty();
        $categoriesList = $useProductCategories 
            ? $prodCats 
            : Category::where('is_active', true)->orderBy('name')->get();

        if ($filterCategoryId) {
            $categoriesList = $categoriesList->where('id', (int) $filterCategoryId);
        }

        $productionGroups = collect();
        foreach ($categoriesList as $cat) {
            $materialsQuery = Material::query()
                ->where('is_active', true)
                ->when($useProductCategories, function ($q) use ($cat) {
                    $q->where(function ($catQ) use ($cat) {
                        $catQ->where('product_category_id', $cat->id)
                             ->orWhere(fn ($sq) => $sq->whereNull('product_category_id')->where('category_id', $cat->id));
                    });
                }, function ($q) use ($cat) {
                    $q->where('category_id', $cat->id);
                })
                ->when($search !== '', function ($q) use ($search) {
                    $q->where(function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                          ->orWhere('item_code', 'like', "%{$search}%");
                    });
                })
                ->with(['recipes.material', 'lots' => function ($q) {
                    $q->latest()->limit(1);
                }, 'stockMovements' => function ($q) {
                    $q->where('movement_type', 'production_output')->latest()->limit(1);
                }]);

            $products = $materialsQuery->get();

            // When searching or filtering by category, skip groups that have no matching products
            if (($search !== '' || $filterCategoryId) && $products->isEmpty()) {
                continue;
            }

            $productionGroups->push([
                'category_id' => $cat->id,
                'name' => $cat->name,
                'products' => $products,
                'count' => $products->count(),
            ]);
        }

        $formCategories = ProductCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if ($formCategories->isEmpty()) {
            $formCategories = Category::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        $year = now()->format('Y');
        $countThisYear = ProductionOrder::whereYear('created_at', $year)->count();
        $defaultBatchNo = "PB-{$year}-" . str_pad($countThisYear + 1, 4, '0', STR_PAD_LEFT);

        $materials = Material::where('is_active', true)
            ->with('productCategory')
            ->withSum(['lots as usable_stock' => function ($query) {
                $query->where('quantity_on_hand', '>', 0)
                    ->where(fn ($q) => $q->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', today()));
            }], 'quantity_on_hand')
            ->orderBy('item_code')
            ->orderBy('name')
            ->get();

        $createOpen = $request->routeIs('production.create') 
            || ($request->has('create') ? $request->boolean('create') : true);

        return view('production.index', [
            'categories' => $formCategories,
            'formCategories' => $formCategories,
            'materials' => $materials,
            'defaultBatchNo' => $defaultBatchNo,
            'productionGroups' => $productionGroups,
            'createOpen' => $createOpen,
            'selectedCategoryId' => session('selected_category') ?? $request->query('selected_category') ?? $request->query('category_id'),
        ]);
    }

    public function create(Request $request): View
    {
        return $this->index($request)->with([
            'createOpen' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $isNewFormat = $request->has('product_id') 
            || $request->has('new_production_name') 
            || $request->has('ingredients')
            || $request->has('category_id')
            || $request->has('new_product_category');

        if ($isNewFormat) {
            $data = $request->validate([
                'editing_product_id' => ['nullable', 'integer'],
                'category_id' => ['nullable'],
                'new_product_category' => ['nullable', 'string', 'max:100'],
                'product_id' => ['nullable'],
                'new_production_name' => ['nullable', 'string', 'max:255'],
                'product_unit' => ['nullable', 'string', 'max:20'],
                'quantity' => ['required', 'numeric', 'gt:0'],
                'batch_number' => ['nullable', 'string', 'max:50'],
                'expiry_date' => ['nullable', 'date'],
                'notes' => ['nullable', 'string'],
                'produced_at' => ['nullable', 'date'],
                'ingredients' => ['nullable', 'array'],
                'ingredients.*.material_id' => ['nullable', Rule::exists('materials', 'id')->where('is_active', true)->whereNull('deleted_at')],
                'ingredients.*.quantity' => ['nullable', 'numeric', 'gt:0'],
                'ingredients.*.unit' => ['nullable', 'string', 'max:20'],
            ]);

            // If editing an existing product card
            if (! empty($data['editing_product_id'])) {
                $productMaterial = Material::findOrFail($data['editing_product_id']);

                // Resolve category if changed
                $hasCategoryId = ! empty($data['category_id']) && $data['category_id'] !== '__new__';
                $hasNewCategory = ! empty($data['new_product_category']) && trim($data['new_product_category']) !== '';
                $productCategory = null;
                if ($hasNewCategory) {
                    $productCategory = ProductCategory::firstOrCreate([
                        'name' => trim($data['new_product_category']),
                    ], ['is_active' => true]);
                } elseif ($hasCategoryId) {
                    $productCategory = ProductCategory::find($data['category_id'])
                        ?? Category::find($data['category_id']);
                }
                if ($productCategory) {
                    $productMaterial->update(['product_category_id' => $productCategory->id, 'type' => 'product']);
                } else {
                    $productMaterial->update(['type' => 'product']);
                }

                // If renamed
                if (! empty($data['new_production_name']) && trim($data['new_production_name']) !== '') {
                    $productMaterial->update(['name' => trim($data['new_production_name'])]);
                }

                // Parse ingredients
                $inputs = [];
                foreach ($data['ingredients'] ?? [] as $row) {
                    if (! empty($row['material_id']) && ! empty($row['quantity']) && (float) $row['quantity'] > 0) {
                        $inputs[] = [
                            'material_id' => (int) $row['material_id'],
                            'quantity' => (float) $row['quantity'],
                            'unit' => $row['unit'] ?? 'kg',
                        ];
                    }
                }

                // Sync recipe
                ProductRecipe::where('product_id', $productMaterial->id)->delete();
                foreach ($inputs as $input) {
                    ProductRecipe::create([
                        'product_id' => $productMaterial->id,
                        'material_id' => $input['material_id'],
                        'quantity' => $input['quantity'],
                    ]);
                }

                $batchNumber = ! empty($data['batch_number']) ? trim($data['batch_number']) : null;
                $lot = null;
                if ($batchNumber) {
                    $lot = MaterialLot::where('material_id', $productMaterial->id)->where('lot_number', $batchNumber)->first();
                }
                if (! $lot) {
                    $lot = $productMaterial->lots()->latest()->first();
                }
                if ($lot) {
                    $lot->update([
                        'quantity_on_hand' => $data['quantity'],
                        'quantity_received' => $data['quantity'],
                        'lot_number' => $batchNumber ?: $lot->lot_number,
                        'expiry_date' => $data['expiry_date'] ?? $lot->expiry_date,
                        'notes' => $data['notes'] ?? $lot->notes,
                    ]);
                    $movement = StockMovement::where('material_lot_id', $lot->id)
                        ->where('movement_type', 'production_output')
                        ->latest()
                        ->first();
                    if ($movement) {
                        $movement->update([
                            'quantity' => $data['quantity'],
                            'balance_after' => $data['quantity'],
                            'notes' => $data['notes'] ?: $movement->notes,
                        ]);
                    }
                } else {
                    $batchNo = $batchNumber ?: ("PB-" . date('Y') . "-0001");
                    $lot = MaterialLot::create([
                        'material_id' => $productMaterial->id,
                        'lot_number' => $batchNo,
                        'quantity_received' => $data['quantity'],
                        'quantity_on_hand' => $data['quantity'],
                        'expiry_date' => $data['expiry_date'] ?? null,
                        'manufactured_date' => today()->toDateString(),
                        'unit_cost' => $productMaterial->cost_per_unit,
                        'status' => 'active',
                        'notes' => $data['notes'] ?: ('Production batch ' . $batchNo),
                    ]);
                    StockMovement::create([
                        'material_id' => $productMaterial->id,
                        'material_lot_id' => $lot->id,
                        'movement_type' => 'production_output',
                        'quantity' => $data['quantity'],
                        'balance_after' => $data['quantity'],
                        'reference_type' => 'production',
                        'reference_id' => $lot->id,
                        'notes' => 'Production batch ' . $batchNo,
                        'created_by' => $request->user()->id,
                    ]);
                }

                return redirect()->route('production.index')
                    ->with('selected_category', $productMaterial->product_category_id)
                    ->with('success_title', 'Production Updated')
                    ->with('success', "Production details for {$productMaterial->name} have been updated.");
            }

            // Validate category selection or new category name
            $hasCategoryId = ! empty($data['category_id']) && $data['category_id'] !== '__new__';
            $hasNewCategory = ! empty($data['new_product_category']) && trim($data['new_product_category']) !== '';
            if (! $hasCategoryId && ! $hasNewCategory) {
                throw ValidationException::withMessages([
                    'category_id' => 'Please select a product category or enter a new one.',
                ]);
            }

            // Validate product selection or new production name
            $hasProductId = ! empty($data['product_id']) && $data['product_id'] !== '__new__';
            $hasNewProduct = ! empty($data['new_production_name']) && trim($data['new_production_name']) !== '';
            if (! $hasProductId && ! $hasNewProduct) {
                throw ValidationException::withMessages([
                    'product_id' => 'Please select a product or enter a new production name.',
                ]);
            }

            // Resolve or create Product Category
            $productCategory = null;
            if ($hasNewCategory) {
                $productCategory = ProductCategory::firstOrCreate([
                    'name' => trim($data['new_product_category']),
                ], [
                    'is_active' => true,
                ]);
            } elseif ($hasCategoryId) {
                $productCategory = ProductCategory::find($data['category_id']);
                if (! $productCategory) {
                    $legacyCat = Category::find($data['category_id']);
                    if ($legacyCat) {
                        $productCategory = ProductCategory::firstOrCreate([
                            'name' => $legacyCat->name,
                        ], [
                            'is_active' => true,
                        ]);
                    }
                }
            }

            // Resolve or create Material (Product)
            $productMaterial = null;
            if ($hasNewProduct) {
                $prodName = trim($data['new_production_name']);

                // Auto-generate unique item code from words (e.g. Hand Wash -> HW-001)
                $words = preg_split('/[\s\-_]+/', $prodName);
                $prefix = '';
                foreach ($words as $w) {
                    if (! empty($w)) {
                        $prefix .= Str::upper(substr($w, 0, 1));
                    }
                }
                if (strlen($prefix) < 2) {
                    $prefix = Str::upper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $prodName), 0, 3));
                }
                if (empty($prefix)) {
                    $prefix = 'PRD';
                }
                $prefix = substr($prefix, 0, 4);

                $nextNum = 1;
                do {
                    $candidateCode = $prefix . '-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
                    $exists = Material::where('item_code', $candidateCode)->exists();
                    $nextNum++;
                } while ($exists);

                $stockUnit = ! empty($data['product_unit']) ? trim($data['product_unit']) : 'L';

                $productMaterial = Material::create([
                    'name' => $prodName,
                    'item_code' => $candidateCode,
                    'type' => 'product',
                    'product_category_id' => $productCategory?->id,
                    'stock_unit' => $stockUnit,
                    'reorder_level' => 0,
                    'is_active' => true,
                ]);
            } elseif ($hasProductId) {
                $productMaterial = Material::findOrFail($data['product_id']);
                if ($productCategory && $productMaterial->product_category_id !== $productCategory->id) {
                    $productMaterial->update(['product_category_id' => $productCategory->id, 'type' => 'product']);
                } else {
                    $productMaterial->update(['type' => 'product']);
                }
            }

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

            // Filter out empty ingredient rows
            $inputs = [];
            foreach ($data['ingredients'] ?? [] as $row) {
                if (! empty($row['material_id']) && ! empty($row['quantity']) && (float) $row['quantity'] > 0) {
                    $inputs[] = [
                        'material_id' => (int) $row['material_id'],
                        'quantity' => (float) $row['quantity'],
                        'unit' => $row['unit'] ?? 'kg',
                    ];
                }
            }

            $outputs = [
                [
                    'material_id' => $productMaterial->id,
                    'quantity' => $data['quantity'],
                    'lot_number' => $batchNumber,
                    'expiry_date' => $data['expiry_date'] ?? null,
                ]
            ];
            $notes = $data['notes'] ?? null;
            $productCategoryId = $productCategory?->id;
        } else {
            $data = $request->validate([
                'produced_at' => ['required', 'date'],
                'notes' => ['nullable', 'string'],
                'inputs' => ['required', 'array', 'min:1'],
                'inputs.*.material_id' => ['required', Rule::exists('materials', 'id')->where('is_active', true)->whereNull('deleted_at')],
                'inputs.*.quantity' => ['required', 'numeric', 'gt:0'],
                'outputs' => ['required', 'array', 'min:1'],
                'outputs.*.material_id' => ['required', Rule::exists('materials', 'id')->where('is_active', true)->whereNull('deleted_at')],
                'outputs.*.quantity' => ['required', 'numeric', 'gt:0'],
            ]);

            $producedAt = $data['produced_at'];
            $orderNumber = 'PR-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
            $inputs = $data['inputs'];
            $outputs = $data['outputs'];
            $notes = $data['notes'] ?? null;
            $productCategoryId = null;
        }

        $order = DB::transaction(function () use ($orderNumber, $producedAt, $productCategoryId, $notes, $inputs, $outputs, $request, $isNewFormat) {
            $order = ProductionOrder::create([
                'order_number' => $orderNumber,
                'produced_at' => $producedAt,
                'product_category_id' => $productCategoryId,
                'notes' => $notes,
                'created_by' => $request->user()->id,
            ]);

            foreach ($inputs as $input) {
                $material = Material::query()->findOrFail($input['material_id']);
                $remaining = (float) $input['quantity'];
                $lots = MaterialLot::query()->where('material_id', $material->id)
                    ->where('quantity_on_hand', '>', 0)
                    ->where(fn ($query) => $query->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', $producedAt))
                    ->orderByRaw('expiry_date is null')->orderBy('expiry_date')
                    ->lockForUpdate()->get();

                foreach ($lots as $lot) {
                    if ($remaining <= 0) {
                        break;
                    }

                    $used = min($remaining, (float) $lot->quantity_on_hand);
                    $balance = (float) $lot->quantity_on_hand - $used;
                    $lot->update(['quantity_on_hand' => $balance]);
                    StockMovement::create([
                        'material_id' => $material->id,
                        'material_lot_id' => $lot->id,
                        'movement_type' => 'production_consumption',
                        'quantity' => -$used,
                        'balance_after' => $balance,
                        'reference_type' => 'production',
                        'reference_id' => $order->id,
                        'notes' => 'Consumed for '.$orderNumber,
                        'created_by' => $request->user()->id,
                    ]);
                    $remaining -= $used;
                }

                if ($remaining > 0) {
                    $errorKey = $isNewFormat ? 'ingredients' : 'inputs';
                    throw ValidationException::withMessages([
                        $errorKey => "Insufficient unexpired stock for {$material->name}.",
                    ]);
                }

                ProductionOrderItem::create([
                    'production_order_id' => $order->id,
                    'material_id' => $material->id,
                    'direction' => 'input',
                    'quantity' => $input['quantity'],
                ]);
            }

            foreach ($outputs as $index => $output) {
                $material = Material::query()->findOrFail($output['material_id']);
                $lotNumber = $output['lot_number'] ?? ($orderNumber.'-'.($index + 1));

                $baseLotNumber = $lotNumber;
                $counter = 1;
                while (MaterialLot::where('material_id', $material->id)->where('lot_number', $lotNumber)->exists()) {
                    $lotNumber = $baseLotNumber . '-' . $counter;
                    $counter++;
                }

                $lot = MaterialLot::create([
                    'material_id' => $material->id,
                    'lot_number' => $lotNumber,
                    'quantity_received' => $output['quantity'],
                    'quantity_on_hand' => $output['quantity'],
                    'expiry_date' => $output['expiry_date'] ?? null,
                    'manufactured_date' => $producedAt,
                    'unit_cost' => $material->cost_per_unit,
                    'status' => 'active',
                    'notes' => $notes ?: ('Produced by '.$orderNumber),
                ]);

                ProductionOrderItem::create([
                    'production_order_id' => $order->id,
                    'material_id' => $material->id,
                    'direction' => 'output',
                    'quantity' => $output['quantity'],
                    'unit_cost' => $material->cost_per_unit,
                ]);

                StockMovement::create([
                    'material_id' => $material->id,
                    'material_lot_id' => $lot->id,
                    'movement_type' => 'production_output',
                    'quantity' => $output['quantity'],
                    'balance_after' => $lot->quantity_on_hand,
                    'reference_type' => 'production',
                    'reference_id' => $order->id,
                    'notes' => 'Produced by '.$orderNumber,
                    'created_by' => $request->user()->id,
                ]);
            }

            return $order;
        });

        if ($isNewFormat && isset($productMaterial) && ! empty($inputs)) {
            ProductRecipe::where('product_id', $productMaterial->id)->delete();
            foreach ($inputs as $input) {
                ProductRecipe::create([
                    'product_id' => $productMaterial->id,
                    'material_id' => $input['material_id'],
                    'quantity' => $input['quantity'],
                ]);
            }
        }

        return redirect()->route('production.index')
            ->with('selected_category', $productCategoryId)
            ->with('success_title', 'Production Completed')
            ->with('success', "Production {$order->order_number} was completed and stock updated.");
    }
}
