@extends('layouts.app')
@section('title', 'Adjust stock')
@section('subtitle', 'Record a traceable correction against a specific material lot.')
@section('content')
<div class="detail-grid"><form method="POST" action="{{ route('adjustments.store') }}" class="panel form-panel">@csrf
<div class="form-section"><span class="eyebrow">INVENTORY CORRECTION</span><h2>Adjustment details</h2><p class="form-hint">Every correction is permanently recorded in the stock movement history.</p><div class="form-grid">
<label class="span-2">Material lot <span class="required">*</span><select name="material_lot_id" required><option value="">Select a lot</option>@foreach($lots as $lot)<option value="{{ $lot->id }}" @selected(old('material_lot_id') == $lot->id)>{{ $lot->material->name }} · {{ $lot->lot_number }} — {{ number_format((float)$lot->quantity_on_hand, 3) }} {{ $lot->material->stock_unit }}</option>@endforeach</select></label>
<label>Adjustment <span class="required">*</span><select name="direction" required><option value="in" @selected(old('direction') === 'in')>Increase stock</option><option value="out" @selected(old('direction') === 'out')>Decrease stock</option></select></label><label>Quantity <span class="required">*</span><input type="number" name="quantity" min="0.001" step="0.001" value="{{ old('quantity') }}" required></label><label class="span-2">Reason / notes <span class="required">*</span><textarea name="notes" rows="4" required>{{ old('notes') }}</textarea></label>
</div></div><div class="form-actions"><a class="button button-secondary" href="{{ route('movements.index') }}">Cancel</a><button class="button button-primary" type="submit">Record adjustment <span>→</span></button></div></form>
<aside class="panel quick-panel"><span class="eyebrow">IMPORTANT</span><h2>Stock integrity</h2><p>Decreases cannot exceed the current lot balance. For expired material, record a decrease with the disposal reason.</p><a class="text-link" href="{{ route('movements.index') }}">Review movement history <span>→</span></a></aside></div>
@endsection
