<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialLot;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockController extends Controller
{
    public function lots(Request $request): View
    {
        $lots = MaterialLot::query()->with('material')->whereHas('material')
            ->when($request->input('filter') === 'expiring', fn ($query) => $query
                ->whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [today(), today()->addDays(90)]))
            ->when($request->input('filter') === 'expired', fn ($query) => $query
                ->whereNotNull('expiry_date')->where('expiry_date', '<', today()))
            ->when($request->input('filter') === 'out', fn ($query) => $query->where('quantity_on_hand', '<=', 0))
            ->orderByRaw('expiry_date is null')->orderBy('expiry_date')
            ->paginate(20)->withQueryString();

        return view('stock.lots', compact('lots'));
    }

    public function createLot(Request $request): View
    {
        return view('stock.receive-lot', [
            'materials' => Material::where('is_active', true)->orderBy('name')->get(),
            'selectedMaterial' => $request->integer('material_id'),
        ]);
    }

    public function storeLot(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'material_id' => ['required', Rule::exists('materials', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'lot_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('material_lots', 'lot_number')->where('material_id', $request->input('material_id')),
            ],
            'manufactured_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:manufactured_date'],
            'quantity_received' => ['required', 'numeric', 'gt:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'supplier_lot_number' => ['nullable', 'string', 'max:255'],
            'storage_location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data, $request) {
            $material = Material::query()->lockForUpdate()->findOrFail($data['material_id']);
            $lot = MaterialLot::create([
                ...$data,
                'quantity_on_hand' => $data['quantity_received'],
                'status' => 'active',
            ]);
            StockMovement::create([
                'material_id' => $material->id,
                'material_lot_id' => $lot->id,
                'movement_type' => 'opening_stock',
                'quantity' => $data['quantity_received'],
                'balance_after' => $lot->quantity_on_hand,
                'notes' => 'Opening stock lot',
                'created_by' => $request->user()->id,
            ]);
        });

        return redirect()->route('lots.index')
            ->with('success_title', 'Stock Lot Added')
            ->with('success', "Lot “{$data['lot_number']}” was added and opening stock recorded.");
    }

    public function adjustments(): View
    {
        return view('stock.adjustment', [
            'lots' => MaterialLot::with('material')->whereHas('material')->where('quantity_on_hand', '>', 0)->orderBy('lot_number')->get(),
        ]);
    }

    public function adjust(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'material_lot_id' => ['required', 'exists:material_lots,id'],
            'direction' => ['required', Rule::in(['in', 'out'])],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'notes' => ['required', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($data, $request) {
            $lot = MaterialLot::query()->lockForUpdate()->findOrFail($data['material_lot_id']);
            $delta = $data['direction'] === 'in' ? (float) $data['quantity'] : -(float) $data['quantity'];
            $balance = (float) $lot->quantity_on_hand + $delta;

            if ($balance < 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'quantity' => 'The adjustment cannot reduce lot stock below zero.',
                ]);
            }

            $lot->update(['quantity_on_hand' => $balance]);
            StockMovement::create([
                'material_id' => $lot->material_id,
                'material_lot_id' => $lot->id,
                'movement_type' => $data['direction'] === 'in' ? 'adjustment_in' : 'adjustment_out',
                'quantity' => $delta,
                'balance_after' => $balance,
                'notes' => $data['notes'],
                'created_by' => $request->user()->id,
            ]);
        });

        return redirect()->route('movements.index')
            ->with('success_title', 'Stock Adjustment Recorded')
            ->with('success', 'The stock adjustment was recorded successfully.');
    }

    public function movements(Request $request): View
    {
        $movements = StockMovement::query()
            ->with(['material', 'materialLot', 'createdBy'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->whereHas('material', fn ($materials) => $materials
                    ->where('name', 'like', '%'.$request->string('q').'%')
                    ->orWhere('item_code', 'like', '%'.$request->string('q').'%'));
            })
            ->when($request->filled('type'), fn ($query) => $query->where('movement_type', $request->string('type')))
            ->latest()->paginate(25)->withQueryString();

        return view('stock.movements', compact('movements'));
    }
}
