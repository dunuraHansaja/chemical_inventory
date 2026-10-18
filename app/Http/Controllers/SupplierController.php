<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $suppliers = Supplier::query()
            ->withCount('materials')
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%')
                ->orWhere('supplier_code', 'like', '%'.$request->string('q').'%'))
            ->orderBy('name')->paginate(15)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('suppliers.form', ['supplier' => new Supplier(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $supplier = Supplier::create($this->validated($request));

        return redirect()->route('suppliers.index')
            ->with('success_title', 'Supplier Added')
            ->with('success', "The supplier “{$supplier->name}” was added successfully.");
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.form', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($this->validated($request, $supplier));

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->purchaseOrders()->exists() || $supplier->materials()->exists()) {
            return back()->withErrors(['supplier' => 'This supplier is in use. Deactivate it instead of deleting it.']);
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('warning', "The supplier “{$supplier->name}” was deleted.");
    }

    private function validated(Request $request, ?Supplier $supplier = null): array
    {
        $data = $request->validate([
            'supplier_code' => ['required', 'string', 'max:50', Rule::unique('suppliers', 'supplier_code')->ignore($supplier?->id)],
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
