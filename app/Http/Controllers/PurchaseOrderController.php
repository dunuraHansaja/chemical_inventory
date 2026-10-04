<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialLot;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(): View
    {
        $orders = PurchaseOrder::with(['supplier', 'items'])->latest()->paginate(20);

        return view('purchases.index', compact('orders'));
    }

    public function create(): View
    {
        return view('purchases.form', $this->formData(new PurchaseOrder(['ordered_at' => today()])));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $order = DB::transaction(function () use ($data, $request) {
            $order = PurchaseOrder::create([
                'order_number' => 'PO-'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
                'supplier_id' => $data['supplier_id'],
                'ordered_at' => $data['ordered_at'],
                'invoice_number' => $data['invoice_number'] ?? null,
                'vat_rate' => $data['vat_rate'] ?? $this->defaultVatRate(),
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);
            $this->saveItems($order, $data['items']);

            return $order;
        });

        return redirect()->route('purchases.show', $order)->with('success', 'Purchase order saved as a draft.');
    }

    public function show(PurchaseOrder $purchase): View
    {
        $purchase->load(['supplier', 'items.material', 'createdBy']);

        return view('purchases.show', compact('purchase'));
    }

    public function edit(PurchaseOrder $purchase): View|RedirectResponse
    {
        if ($purchase->status !== 'draft') {
            return redirect()->route('purchases.show', $purchase)->withErrors(['purchase' => 'Only draft purchases can be edited.']);
        }

        return view('purchases.form', $this->formData($purchase->load('items')));
    }

    public function update(Request $request, PurchaseOrder $purchase): RedirectResponse
    {
        abort_unless($purchase->status === 'draft', 403);
        $data = $this->validated($request);

        DB::transaction(function () use ($data, $purchase) {
            $purchase->update([
                'supplier_id' => $data['supplier_id'],
                'ordered_at' => $data['ordered_at'],
                'invoice_number' => $data['invoice_number'] ?? null,
                'vat_rate' => $data['vat_rate'] ?? $this->defaultVatRate(),
                'notes' => $data['notes'] ?? null,
            ]);
            $purchase->items()->delete();
            $this->saveItems($purchase, $data['items']);
        });

        return redirect()->route('purchases.show', $purchase)->with('success', 'Draft purchase updated.');
    }

    public function destroy(PurchaseOrder $purchase): RedirectResponse
    {
        if ($purchase->status !== 'draft') {
            return back()->withErrors(['purchase' => 'Only draft purchases can be deleted.']);
        }

        $purchase->delete();

        return redirect()->route('purchases.index')->with('success', 'Draft purchase deleted.');
    }

    public function receive(Request $request, PurchaseOrder $purchase): RedirectResponse
    {
        DB::transaction(function () use ($request, $purchase) {
            $purchase = PurchaseOrder::query()->lockForUpdate()->with('items')->findOrFail($purchase->id);
            if ($purchase->status !== 'draft') {
                throw \Illuminate\Validation\ValidationException::withMessages(['purchase' => 'This purchase has already been received.']);
            }

            foreach ($purchase->items as $item) {
                $remaining = (float) $item->quantity - (float) $item->received_quantity;
                if ($remaining <= 0) {
                    continue;
                }

                $lot = MaterialLot::create([
                    'material_id' => $item->material_id,
                    'lot_number' => $purchase->order_number.'-'.$item->id,
                    'quantity_received' => $remaining,
                    'quantity_on_hand' => $remaining,
                    'unit_cost' => $item->unit_cost,
                    'status' => 'active',
                    'notes' => 'Received from '.$purchase->order_number,
                ]);
                $item->update(['received_quantity' => $item->quantity]);
                StockMovement::create([
                    'material_id' => $item->material_id,
                    'material_lot_id' => $lot->id,
                    'movement_type' => 'purchase_receipt',
                    'quantity' => $remaining,
                    'balance_after' => $remaining,
                    'reference_type' => 'purchase',
                    'reference_id' => $purchase->id,
                    'notes' => 'Received '.$purchase->order_number,
                    'created_by' => $request->user()->id,
                ]);
            }

            $purchase->update(['status' => 'received']);
        });

        return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase received and stock lots created.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')->where('is_active', true)],
            'ordered_at' => ['required', 'date'],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'vat_rate' => ['nullable', 'numeric', 'between:0,100'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.material_id' => ['required', Rule::exists('materials', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);
    }

    private function saveItems(PurchaseOrder $order, array $items): void
    {
        foreach ($items as $item) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $order->id,
                'material_id' => $item['material_id'],
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'],
            ]);
        }
    }

    private function formData(PurchaseOrder $purchase): array
    {
        return [
            'purchase' => $purchase,
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
            'materials' => Material::where('is_active', true)->orderBy('name')->get(),
            'defaultVatRate' => DB::table('inventory_settings')->where('key', 'vat_rate')->value('value') ?? '0',
        ];
    }

    private function defaultVatRate(): string
    {
        return (string) (DB::table('inventory_settings')->where('key', 'vat_rate')->value('value') ?? '0');
    }
}
