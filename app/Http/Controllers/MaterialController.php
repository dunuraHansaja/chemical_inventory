<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Material;
use App\Models\Supplier;
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
        $materials = Material::query()
            ->with(['category', 'preferredSupplier'])
            ->withSum('lots', 'quantity_on_hand')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(fn ($search) => $search
                    ->where('name', 'like', $term)
                    ->orWhere('item_code', 'like', $term)
                    ->orWhere('cas_number', 'like', $term));
            })
            ->when($request->input('filter') === 'low', function ($query) {
                $query->whereRaw('(select coalesce(sum(quantity_on_hand), 0) from material_lots where material_lots.material_id = materials.id) <= reorder_level');
            })
            ->when($request->input('filter') === 'expiry', function ($query) {
                $query->whereHas('lots', fn ($lots) => $lots->whereNotNull('expiry_date')
                    ->whereBetween('expiry_date', [today(), today()->addDays(90)])
                    ->where('quantity_on_hand', '>', 0));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('materials.index', compact('materials'));
    }

    public function create(): View
    {
        return view('materials.form', $this->formData(new Material(['is_active' => true])));
    }

    public function store(Request $request): RedirectResponse
    {
        $material = Material::create($this->validated($request));

        return redirect()->route('materials.show', $material)->with('success', 'Material created.');
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

        return redirect()->route('materials.show', $material)->with('success', 'Material updated.');
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

        return redirect()->route('materials.index')->with('success', 'Material archived.');
    }

    public function export(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['item_code', 'name', 'cas_number', 'category', 'stock_unit', 'reorder_level', 'cost_per_unit', 'storage_location', 'is_active']);
            Material::with('category')->orderBy('item_code')->chunk(200, function ($materials) use ($output) {
                foreach ($materials as $material) {
                    fputcsv($output, [
                        $material->item_code, $material->name, $material->cas_number,
                        $material->category?->name, $material->stock_unit, $material->reorder_level,
                        $material->cost_per_unit, $material->storage_location, $material->is_active ? 1 : 0,
                    ]);
                }
            });
            fclose($output);
        }, 'materials.csv', ['Content-Type' => 'text/csv']);
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

        return redirect()->route('materials.index')->with('success', "{$count} materials imported.");
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
