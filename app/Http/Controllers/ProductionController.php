<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialLot;
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
    public function index(): View
    {
        $orders = ProductionOrder::with('items.material')->latest()->paginate(20);

        return view('production.index', compact('orders'));
    }

    public function create(): View
    {
        return view('production.form', [
            'materials' => Material::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
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

        $order = DB::transaction(function () use ($data, $request) {
            $orderNumber = 'PR-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
            $order = ProductionOrder::create([
                'order_number' => $orderNumber,
                'produced_at' => $data['produced_at'],
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($data['inputs'] as $input) {
                $material = Material::query()->findOrFail($input['material_id']);
                $remaining = (float) $input['quantity'];
                $lots = MaterialLot::query()->where('material_id', $material->id)
                    ->where('quantity_on_hand', '>', 0)
                    ->where(fn ($query) => $query->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', $data['produced_at']))
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
                    throw ValidationException::withMessages([
                        'inputs' => "Insufficient unexpired stock for {$material->name}.",
                    ]);
                }

                ProductionOrderItem::create([
                    'production_order_id' => $order->id,
                    'material_id' => $material->id,
                    'direction' => 'input',
                    'quantity' => $input['quantity'],
                ]);
            }

            foreach ($data['outputs'] as $index => $output) {
                $material = Material::query()->findOrFail($output['material_id']);
                $lot = MaterialLot::create([
                    'material_id' => $material->id,
                    'lot_number' => $orderNumber.'-'.($index + 1),
                    'quantity_received' => $output['quantity'],
                    'quantity_on_hand' => $output['quantity'],
                    'unit_cost' => $material->cost_per_unit,
                    'status' => 'active',
                    'notes' => 'Produced by '.$orderNumber,
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

        return redirect()->route('production.index')
            ->with('success_title', 'Production Completed')
            ->with('success', "Production {$order->order_number} was completed and stock updated.");
    }
}
