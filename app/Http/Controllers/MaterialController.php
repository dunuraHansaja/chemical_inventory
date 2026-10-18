<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialLot;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MaterialController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in(['in_stock', 'low_stock', 'out_of_stock', 'inactive', 'expiring'])],
            'filter' => ['nullable', Rule::in(['low', 'expiry'])],
            'sort' => ['nullable', Rule::in(['code', 'material', 'cas', 'category', 'on_hand', 'reorder', 'expiry', 'location', 'status'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', Rule::in(['10', '25', '50', '100'])],
        ]);

        $materials = $this->listingQuery($request)
            ->orderByRaw($this->sortExpression($filters['sort'] ?? 'material').' '.($filters['direction'] ?? 'asc'))
            ->orderBy('materials.id')
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();

        $stockCounts = DB::query()->fromSub(
            DB::table('materials')
                ->leftJoin('material_lots', 'material_lots.material_id', '=', 'materials.id')
                ->whereNull('materials.deleted_at')
                ->groupBy('materials.id', 'materials.is_active', 'materials.reorder_level')
                ->select([
                    'materials.id',
                    'materials.is_active',
                    'materials.reorder_level',
                ])
                ->selectRaw('COALESCE(SUM(material_lots.quantity_on_hand), 0) as on_hand'),
            'material_stock',
        )->selectRaw('
            SUM(CASE WHEN is_active = 1 AND on_hand > reorder_level THEN 1 ELSE 0 END) as in_stock,
            SUM(CASE WHEN is_active = 1 AND on_hand > 0 AND on_hand <= reorder_level THEN 1 ELSE 0 END) as low_stock,
            SUM(CASE WHEN is_active = 1 AND on_hand <= 0 THEN 1 ELSE 0 END) as out_of_stock
        ')->first();

        $stats = [
            'total' => Material::where('is_active', true)->count(),
            'in_stock' => (int) $stockCounts->in_stock,
            'low_stock' => (int) $stockCounts->low_stock,
            'out_of_stock' => (int) $stockCounts->out_of_stock,
            'expiring' => Material::where('is_active', true)
                ->whereHas('lots', fn ($lots) => $lots
                    ->whereNotNull('expiry_date')
                    ->whereBetween('expiry_date', [today(), today()->addDays(90)])
                    ->where('quantity_on_hand', '>', 0))
                ->count(),
        ];
        $categories = Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('materials.index', compact('materials', 'stats', 'categories'));
    }

    public function create(): View
    {
        return view('materials.form', $this->formData(new Material(['is_active' => true])));
    }

    public function store(Request $request): RedirectResponse
    {
        $material = Material::create($this->validated($request));

        return redirect()->route('materials.show', $material)
            ->with('success_title', 'Material Added')
            ->with('success', "The material “{$material->name}” was added successfully.");
    }

    public function show(Material $material): View
    {
        $material->load(['category', 'preferredSupplier']);
        $lots = $material->lots()->orderByRaw('expiry_date is null')->orderBy('expiry_date')->paginate(15);

        return view('materials.show', compact('material', 'lots'));
    }

    public function edit(Material $material): View
    {
        return view('materials.form', $this->formData($material));
    }

    public function update(Request $request, Material $material): RedirectResponse
    {
        $material->update($this->validated($request, $material));

        return redirect()->route('materials.show', $material)
            ->with('success_title', 'Material Updated')
            ->with('success', "The details for “{$material->name}” were updated successfully.");
    }

    public function destroy(Material $material): RedirectResponse
    {
        if (
            $material->lots()->exists()
            || $material->stockMovements()->exists()
            || $material->purchaseOrderItems()->exists()
            || $material->productionOrderItems()->exists()
        ) {
            return back()->withErrors(['material' => 'This material has inventory history and cannot be archived. Set it inactive instead.']);
        }

        $material->delete();

        return redirect()->route('materials.index')
            ->with('warning', "The material “{$material->name}” was archived.");
    }

    public function export(Request $request): StreamedResponse
    {
        $request->validate([
            'ids' => ['sometimes', 'array', 'max:100'],
            'ids.*' => ['integer', 'distinct', 'exists:materials,id'],
        ]);
        $query = $this->listingQuery($request);
        if ($request->has('ids')) {
            $query->whereIn('materials.id', $request->input('ids'));
        }
        $query->orderBy('materials.item_code');

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['item_code', 'name', 'cas_number', 'category', 'quantity_on_hand', 'stock_unit', 'reorder_level', 'next_expiry', 'storage_location', 'status']);
            $query->chunk(200, function ($materials) use ($output) {
                foreach ($materials as $material) {
                    $onHand = (float) $material->lots_sum_quantity_on_hand;
                    fputcsv($output, [
                        $material->item_code,
                        $material->name,
                        $material->cas_number,
                        $material->category?->name,
                        number_format($onHand, 3, '.', ''),
                        $material->stock_unit,
                        $material->reorder_level,
                        $material->next_expiry_date,
                        $material->storage_location ?: $material->effective_storage_location,
                        $this->materialStatus($material, $onHand),
                    ]);
                }
            });
            fclose($output);
        }, 'materials.csv', ['Content-Type' => 'text/csv']);
    }

    public function print(Request $request): View
    {
        $request->validate([
            'ids' => ['sometimes', 'array', 'max:100'],
            'ids.*' => ['integer', 'distinct', 'exists:materials,id'],
        ]);
        $query = $this->listingQuery($request);
        if ($request->has('ids')) {
            $query->whereIn('materials.id', $request->input('ids'));
        }

        $materials = $query
            ->orderBy('materials.item_code')
            ->get();

        return view('materials.print', compact('materials'));
    }

    private function listingQuery(Request $request): Builder
    {
        $query = Material::query()
            ->with(['category', 'preferredSupplier'])
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

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function (Builder $search) use ($term) {
                $search->where('materials.name', 'like', $term)
                    ->orWhere('materials.item_code', 'like', $term)
                    ->orWhere('materials.cas_number', 'like', $term)
                    ->orWhere('materials.storage_location', 'like', $term)
                    ->orWhereHas('preferredSupplier', fn (Builder $supplier) => $supplier->where('name', 'like', $term))
                    ->orWhereHas('lots', fn (Builder $lot) => $lot->where('storage_location', 'like', $term));
            });
        }

        if ($request->filled('category_id') && ctype_digit((string) $request->input('category_id'))) {
            $query->where('materials.category_id', (int) $request->input('category_id'));
        }

        $quantity = '(select coalesce(sum(material_lots.quantity_on_hand), 0) from material_lots where material_lots.material_id = materials.id)';
        $expiringLot = fn (Builder $lots) => $lots
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [today(), today()->addDays(90)])
            ->where('quantity_on_hand', '>', 0);

        if ($request->input('filter') === 'low') {
            $query->whereRaw($quantity.' <= materials.reorder_level');
        } elseif ($request->input('filter') === 'expiry') {
            $query->whereHas('lots', $expiringLot);
        }

        match ($request->input('status')) {
            'in_stock' => $query->where('materials.is_active', true)->whereRaw($quantity.' > materials.reorder_level'),
            'low_stock' => $query->where('materials.is_active', true)->whereRaw($quantity.' > 0 and '.$quantity.' <= materials.reorder_level'),
            'out_of_stock' => $query->where('materials.is_active', true)->whereRaw($quantity.' <= 0'),
            'inactive' => $query->where('materials.is_active', false),
            'expiring' => $query->whereHas('lots', $expiringLot),
            default => null,
        };

        return $query;
    }

    private function sortExpression(string $sort): string
    {
        return match ($sort) {
            'code' => 'materials.item_code',
            'cas' => 'materials.cas_number',
            'category' => '(select categories.name from categories where categories.id = materials.category_id)',
            'on_hand' => '(select coalesce(sum(material_lots.quantity_on_hand), 0) from material_lots where material_lots.material_id = materials.id)',
            'reorder' => 'materials.reorder_level',
            'expiry' => '(select min(material_lots.expiry_date) from material_lots where material_lots.material_id = materials.id and material_lots.quantity_on_hand > 0)',
            'location' => 'coalesce(materials.storage_location, (select material_lots.storage_location from material_lots where material_lots.material_id = materials.id and material_lots.storage_location is not null order by material_lots.expiry_date limit 1))',
            'status' => 'case when materials.is_active = 0 then 0 when (select coalesce(sum(material_lots.quantity_on_hand), 0) from material_lots where material_lots.material_id = materials.id) <= 0 then 1 when (select coalesce(sum(material_lots.quantity_on_hand), 0) from material_lots where material_lots.material_id = materials.id) <= materials.reorder_level then 2 else 3 end',
            default => 'materials.name',
        };
    }

    private function materialStatus(Material $material, float $onHand): string
    {
        if (! $material->is_active) {
            return 'Inactive';
        }

        if ($onHand <= 0) {
            return 'Out of stock';
        }

        return $onHand <= (float) $material->reorder_level ? 'Low stock' : 'In stock';
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);
        $handle = fopen($request->file('file')->getRealPath(), 'r');

        if ($handle === false) {
            return back()->withErrors(['file' => 'The uploaded CSV could not be read.']);
        }

        $headers = fgetcsv($handle);
        $required = ['item_code', 'name', 'stock_unit', 'reorder_level'];
        if (! is_array($headers) || array_diff($required, $headers)) {
            fclose($handle);

            return back()->withErrors(['file' => 'CSV must include item_code, name, stock_unit and reorder_level columns.']);
        }

        $count = 0;
        DB::transaction(function () use ($handle, $headers, &$count) {
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) !== count($headers)) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['file' => 'A CSV row has a different number of values than the header.']);
                }

                $data = array_combine($headers, $row);
                $data = validator($data, [
                    'item_code' => ['required', 'string', 'max:50', 'unique:materials,item_code'],
                    'name' => ['required', 'string', 'max:255'],
                    'stock_unit' => ['required', 'string', 'max:20'],
                    'reorder_level' => ['required', 'numeric', 'min:0'],
                    'cost_per_unit' => ['nullable', 'numeric', 'min:0'],
                    'cas_number' => ['nullable', 'string', 'max:50'],
                    'storage_location' => ['nullable', 'string', 'max:255'],
                    'category' => ['nullable', 'string', 'max:191'],
                    'is_active' => ['nullable', 'boolean'],
                ])->validate();

                if (! empty($data['category'])) {
                    $category = Category::firstOrCreate(['name' => $data['category']], ['is_active' => true]);
                    $data['category_id'] = $category->id;
                }
                unset($data['category']);

                Material::create($data);
                $count++;
            }
        });
        fclose($handle);

        return redirect()->route('materials.index')
            ->with('success_title', 'Materials Imported')
            ->with('success', "{$count} materials were imported successfully.");
    }

    private function validated(Request $request, ?Material $material = null): array
    {
        $data = $request->validate([
            'item_code' => ['required', 'string', 'max:50', Rule::unique('materials', 'item_code')->ignore($material?->id)],
            'name' => ['required', 'string', 'max:255'],
            'cas_number' => ['nullable', 'string', 'max:50'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'stock_unit' => ['required', 'string', 'max:20'],
            'reorder_level' => ['required', 'numeric', 'min:0'],
            'cost_per_unit' => ['nullable', 'numeric', 'min:0'],
            'storage_location' => ['nullable', 'string', 'max:255'],
            'preferred_supplier_id' => ['nullable', 'exists:suppliers,id'],
            'hazard_classes' => ['nullable', 'string', 'max:500'],
            'handling_notes' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        return array_merge($data, [
            'hazard_classes' => collect(explode(',', (string) $request->input('hazard_classes')))
                ->map(fn ($hazard) => trim($hazard))->filter()->values()->all(),
            'is_active' => $request->boolean('is_active'),
        ]);
    }

    private function formData(Material $material): array
    {
        return [
            'material' => $material,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
            'hazardClasses' => implode(', ', $material->hazard_classes ?? []),
        ];
    }
}
