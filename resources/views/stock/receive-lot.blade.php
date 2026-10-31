@extends('layouts.app')
@section('title', 'Receive a lot')
@section('subtitle', 'Record opening stock or a newly received batch.')
@section('actions')
    <a class="button button-secondary" href="{{ route('lots.index') }}">← Back to lots</a>
@endsection

@section('content')
<form method="POST" action="{{ route('lots.store') }}" class="panel form-panel">
    @csrf

    <div class="form-section">
        <span class="eyebrow">NEW BATCH</span>
        <h2>Lot details</h2>
        <p class="form-hint">Initial quantity is added to available inventory and immediately written to the movement ledger.</p>

        <div class="form-grid">
            <label>Material <span class="required">*</span>
                <select name="material_id" required>
                    <option value="">Select a material</option>
                    @foreach($materials as $material)
                        <option value="{{ $material->id }}" @selected(old('material_id', $selectedMaterial) == $material->id)>
                            {{ $material->item_code }} · {{ $material->name }} ({{ $material->stock_unit }})
                        </option>
                    @endforeach
                </select>
            </label>

            <label>Lot / batch number <span class="required">*</span>
                <input name="lot_number" value="{{ old('lot_number') }}" maxlength="100" placeholder="e.g. LOT-2026-0042" required>
            </label>

            <label>Quantity received <span class="required">*</span>
                <input type="number" name="quantity_received" min="0.001" step="0.001" value="{{ old('quantity_received') }}" placeholder="0.000" required>
            </label>

            <label>Unit cost
                <input type="number" name="unit_cost" min="0" step="0.01" value="{{ old('unit_cost') }}" placeholder="0.00">
            </label>

            <label>Manufactured date
                <input type="date" name="manufactured_date" value="{{ old('manufactured_date') }}">
            </label>

            <label>Expiry date
                <input type="date" name="expiry_date" value="{{ old('expiry_date') }}">
            </label>

            <label>Supplier lot number
                <input name="supplier_lot_number" value="{{ old('supplier_lot_number') }}" placeholder="e.g. SLN-98124">
            </label>

            <label>Storage location
                <input name="storage_location" value="{{ old('storage_location') }}" placeholder="e.g. Warehouse A / Shelf 3-B">
            </label>

            <label class="span-2">Notes
                <textarea name="notes" rows="3" placeholder="Optional notes about batch condition, packaging, or storage requirements...">{{ old('notes') }}</textarea>
            </label>
        </div>
    </div>

    <div class="form-actions">
        <a class="button button-secondary" href="{{ route('lots.index') }}">Cancel</a>
        <button class="button button-primary" type="submit">Receive stock <span>→</span></button>
    </div>
</form>
@endsection
