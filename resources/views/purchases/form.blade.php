@extends('layouts.app')
@section('title', $purchase->exists ? 'Edit purchase' : 'New purchase')
@section('subtitle', 'Create a draft supplier order with the quantities and costs you expect to receive.')
@section('actions')<a class="button button-secondary" href="{{ $purchase->exists ? route('purchases.show', $purchase) : route('purchases.index') }}">← Cancel</a>@endsection
@section('content')
@php($formItems = old('items', $purchase->items->isNotEmpty() ? $purchase->items->map(fn($item) => ['material_id' => $item->material_id, 'quantity' => $item->quantity, 'unit_cost' => $item->unit_cost])->all() : [['material_id' => '', 'quantity' => '', 'unit_cost' => '']]))
<form method="POST" action="{{ $purchase->exists ? route('purchases.update', $purchase) : route('purchases.store') }}" class="panel form-panel" @if($purchase->exists) data-confirm="Save changes to draft purchase {{ $purchase->order_number }}?" data-confirm-title="Save purchase changes?" data-confirm-action="Save Changes" data-confirm-tone="primary" @endif>@csrf @if($purchase->exists)@method('PUT')@endif
<div class="form-section"><span class="eyebrow">SUPPLIER ORDER</span><h2>Order details</h2><div class="form-grid">
<label>Supplier <span class="required">*</span><select name="supplier_id" required><option value="">Select supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(old('supplier_id', $purchase->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>@endforeach</select></label>
<label>Order date <span class="required">*</span><input type="date" name="ordered_at" value="{{ old('ordered_at', $purchase->ordered_at?->format('Y-m-d')) }}" required></label>
<label>Invoice / reference number<input name="invoice_number" value="{{ old('invoice_number', $purchase->invoice_number) }}"></label>
<label>VAT rate (%)<input type="number" name="vat_rate" min="0" max="100" step="0.01" value="{{ old('vat_rate', $purchase->vat_rate ?? $defaultVatRate) }}"><small>Defaults to the rate configured in Settings. Change for this purchase if needed.</small></label>
<label class="span-2">Notes<textarea name="notes" rows="3">{{ old('notes', $purchase->notes) }}</textarea></label>
</div></div>
<div class="form-section"><div class="section-title-row"><div><span class="eyebrow">ORDER LINES</span><h2>Materials to receive</h2><p class="form-hint">Receipt creates one new lot per line and records the movement.</p></div><button class="button button-secondary button-small" type="button" data-add-line="purchase-lines">＋ Add line</button></div>
<div class="line-items" id="purchase-lines">
@foreach($formItems as $index => $line)
<div class="line-item" data-line-row><label>Material<select name="items[{{ $index }}][material_id]" required><option value="">Select material</option>@foreach($materials as $material)<option value="{{ $material->id }}" @selected(($line['material_id'] ?? '') == $material->id)>{{ $material->item_code }} · {{ $material->name }} ({{ $material->stock_unit }})</option>@endforeach</select></label><label>Quantity<input type="number" name="items[{{ $index }}][quantity]" min="0.001" step="0.001" value="{{ $line['quantity'] ?? '' }}" required></label><label>Unit cost<input type="number" name="items[{{ $index }}][unit_cost]" min="0" step="0.01" value="{{ $line['unit_cost'] ?? '' }}" required></label><button type="button" class="icon-button remove-line" data-remove-line aria-label="Remove line">×</button></div>
@endforeach
</div></div>
<div class="form-actions"><a class="button button-secondary" href="{{ $purchase->exists ? route('purchases.show', $purchase) : route('purchases.index') }}">Cancel</a><button class="button button-primary" type="submit">Save draft purchase <span>→</span></button></div>
</form>
<template id="purchase-line-template"><div class="line-item" data-line-row><label>Material<select data-name="material_id" required><option value="">Select material</option>@foreach($materials as $material)<option value="{{ $material->id }}">{{ $material->item_code }} · {{ $material->name }} ({{ $material->stock_unit }})</option>@endforeach</select></label><label>Quantity<input data-name="quantity" type="number" min="0.001" step="0.001" required></label><label>Unit cost<input data-name="unit_cost" type="number" min="0" step="0.01" required></label><button type="button" class="icon-button remove-line" data-remove-line aria-label="Remove line">×</button></div></template>
@endsection
