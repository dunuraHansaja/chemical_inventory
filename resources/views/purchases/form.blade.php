@extends('layouts.app')
@section('title', $purchase->exists ? 'Edit purchase' : 'New purchase')
@section('subtitle', 'Create a draft supplier order with one or multiple items and the quantities and costs you expect to receive.')
@section('actions')
    <a class="button button-secondary" href="{{ $purchase->exists ? route('purchases.show', $purchase) : route('purchases.index') }}">← Cancel</a>
@endsection

@section('content')
<?php
    $formItems = old(
        'items',
        $purchase->items->isNotEmpty()
            ? $purchase->items->map(fn ($item) => [
                'material_id' => $item->material_id,
                'quantity' => number_format((float) $item->quantity, 2, '.', ''),
                'unit_cost' => number_format((float) $item->unit_cost, 2, '.', ''),
            ])->all()
            : [['material_id' => '', 'quantity' => '', 'unit_cost' => '']]
    );
    $currencySymbol = $inventorySettings['currency_symbol'] ?? '$';
    $currencyCode = $inventorySettings['currency_code'] ?? 'USD';
?>

<form method="POST"
      action="{{ $purchase->exists ? route('purchases.update', $purchase) : route('purchases.store') }}"
      class="panel form-panel"
      id="purchase-order-form"
      data-currency-symbol="{{ $currencySymbol }}"
      data-currency-code="{{ $currencyCode }}"
      @if($purchase->exists)
          data-confirm="Save changes to draft purchase {{ $purchase->order_number }}?"
          data-confirm-title="Save purchase changes?"
          data-confirm-action="Save Changes"
          data-confirm-tone="primary"
      @endif>
    @csrf
    @if($purchase->exists)
        @method('PUT')
    @endif

    <div class="form-section">
        <span class="eyebrow">SUPPLIER ORDER</span>
        <h2>Order details</h2>
        <p class="form-hint">Specify the supplier and general order terms for this purchase order.</p>

        <div class="form-grid">
            <label>Supplier <span class="required">*</span>
                <select name="supplier_id" id="purchase-supplier-select" data-purchase-supplier required>
                    <option value="">Select supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('supplier_id', $purchase->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </label>

            <label>Order date <span class="required">*</span>
                <input type="date" name="ordered_at" value="{{ old('ordered_at', $purchase->ordered_at?->format('Y-m-d')) }}" required>
            </label>

            <label>Invoice / reference number
                <input name="invoice_number" value="{{ old('invoice_number', $purchase->invoice_number) }}" placeholder="e.g. INV-2026-001">
            </label>

            <label>VAT rate (%)
                <input type="number" name="vat_rate" id="purchase-vat-input" data-purchase-vat min="0" max="100" step="0.01" value="{{ old('vat_rate', $purchase->vat_rate ?? $defaultVatRate) }}" placeholder="0.00">
                <small>Defaults to the rate configured in Settings. Change for this purchase if needed.</small>
            </label>

            <label class="span-2">Notes
                <textarea name="notes" rows="3" placeholder="Optional notes, delivery terms, or PO instructions...">{{ old('notes', $purchase->notes) }}</textarea>
            </label>
        </div>
    </div>

    <div class="form-section">
        <div class="section-title-row purchase-lines-header">
            <div>
                <span class="eyebrow">ORDER LINES</span>
                <h2>Materials to receive</h2>
                <p class="form-hint">Add one or multiple items purchased at the same time from this supplier. Receipt creates one traceable lot per line.</p>
            </div>
            <div class="purchase-lines-toolbar">
                <span class="purchase-items-count-badge" data-purchase-item-count>
                    {{ count($formItems) }} {{ count($formItems) === 1 ? 'item' : 'items' }}
                </span>
                <button class="button button-secondary button-small" type="button" data-add-supplier-items hidden title="Add all catalogue items linked to the selected supplier">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                    <span>Add supplier items</span>
                </button>
                <button class="button button-secondary button-small" type="button" data-add-line="purchase-lines">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    <span>Add line</span>
                </button>
            </div>
        </div>

        <div class="line-items" id="purchase-lines">
            @foreach($formItems as $index => $line)
                <?php
                    $lineQty = isset($line['quantity']) && $line['quantity'] !== '' ? number_format((float) $line['quantity'], 2, '.', '') : '';
                    $lineCost = isset($line['unit_cost']) && $line['unit_cost'] !== '' ? number_format((float) $line['unit_cost'], 2, '.', '') : '';
                    $lineTotal = ((float) ($line['quantity'] ?? 0)) * ((float) ($line['unit_cost'] ?? 0));
                ?>
                <div class="line-item purchase-line-item" data-line-row>
                    <span class="purchase-line-badge" data-line-index aria-label="Line {{ $index + 1 }}">#{{ $index + 1 }}</span>

                    <label>Material <span class="required">*</span>
                        <select name="items[{{ $index }}][material_id]" data-purchase-material required>
                            <option value="">Select material</option>
                            @foreach($materials as $material)
                                <option value="{{ $material->id }}"
                                        data-supplier-id="{{ $material->preferred_supplier_id }}"
                                        data-unit="{{ $material->stock_unit }}"
                                        data-cost="{{ $material->cost_per_unit !== null ? number_format((float) $material->cost_per_unit, 2, '.', '') : '' }}"
                                        data-base-label="{{ $material->item_code }} · {{ $material->name }} ({{ $material->stock_unit }})"
                                        @selected(($line['material_id'] ?? '') == $material->id)>
                                    {{ $material->item_code }} · {{ $material->name }} ({{ $material->stock_unit }})
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span class="purchase-field-label-inline">
                            <span>Quantity <span class="required">*</span></span>
                            <small class="purchase-unit-pill" data-purchase-unit></small>
                        </span>
                        <input type="number"
                               name="items[{{ $index }}][quantity]"
                               data-purchase-qty
                               min="0.01"
                               step="0.01"
                               value="{{ $lineQty }}"
                               placeholder="0.00"
                               required>
                    </label>

                    <label>Unit cost ({{ $currencySymbol }}) <span class="required">*</span>
                        <input type="number"
                               name="items[{{ $index }}][unit_cost]"
                               data-purchase-cost
                               min="0"
                               step="0.01"
                               value="{{ $lineCost }}"
                               placeholder="0.00"
                               required>
                    </label>

                    <div class="purchase-line-total-box">
                        <span class="purchase-line-total-label">Line total</span>
                        <strong class="purchase-line-total-value" data-purchase-line-total>{{ $currencySymbol }}{{ number_format($lineTotal, 2) }}</strong>
                    </div>

                    <button type="button" class="icon-button remove-line" data-remove-line aria-label="Remove line" title="Remove line">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
            @endforeach
        </div>

        <button type="button" class="purchase-add-row-banner" data-add-line="purchase-lines">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            <span>Add another item from this supplier</span>
        </button>

        <div class="purchase-live-summary" aria-live="polite">
            <div class="purchase-summary-metric">
                <span>Items &amp; Total Qty</span>
                <strong data-summary-items>{{ count($formItems) }} {{ count($formItems) === 1 ? 'item' : 'items' }} · 0.00</strong>
            </div>
            <div class="purchase-summary-metric">
                <span>Subtotal</span>
                <strong data-summary-subtotal>{{ $currencySymbol }}0.00</strong>
            </div>
            <div class="purchase-summary-metric">
                <span>VAT (<span data-summary-vat-rate>{{ number_format((float) old('vat_rate', $purchase->vat_rate ?? $defaultVatRate), 2) }}</span>%)</span>
                <strong data-summary-vat>{{ $currencySymbol }}0.00</strong>
            </div>
            <div class="purchase-summary-metric purchase-summary-grand">
                <span>Estimated Total</span>
                <strong data-summary-total>{{ $currencySymbol }}0.00 {{ $currencyCode }}</strong>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a class="button button-secondary" href="{{ $purchase->exists ? route('purchases.show', $purchase) : route('purchases.index') }}">Cancel</a>
        <button class="button button-primary" type="submit">Save draft purchase <span>→</span></button>
    </div>
</form>

<template id="purchase-lines-template">
    <div class="line-item purchase-line-item" data-line-row>
        <span class="purchase-line-badge" data-line-index>#1</span>

        <label>Material <span class="required">*</span>
            <select data-name="material_id" data-purchase-material required>
                <option value="">Select material</option>
                @foreach($materials as $material)
                    <option value="{{ $material->id }}"
                            data-supplier-id="{{ $material->preferred_supplier_id }}"
                            data-unit="{{ $material->stock_unit }}"
                            data-cost="{{ $material->cost_per_unit !== null ? number_format((float) $material->cost_per_unit, 2, '.', '') : '' }}"
                            data-base-label="{{ $material->item_code }} · {{ $material->name }} ({{ $material->stock_unit }})">
                        {{ $material->item_code }} · {{ $material->name }} ({{ $material->stock_unit }})
                    </option>
                @endforeach
            </select>
        </label>

        <label>
            <span class="purchase-field-label-inline">
                <span>Quantity <span class="required">*</span></span>
                <small class="purchase-unit-pill" data-purchase-unit></small>
            </span>
            <input data-name="quantity" data-purchase-qty type="number" min="0.01" step="0.01" placeholder="0.00" required>
        </label>

        <label>Unit cost ({{ $currencySymbol }}) <span class="required">*</span>
            <input data-name="unit_cost" data-purchase-cost type="number" min="0" step="0.01" placeholder="0.00" required>
        </label>

        <div class="purchase-line-total-box">
            <span class="purchase-line-total-label">Line total</span>
            <strong class="purchase-line-total-value" data-purchase-line-total>{{ $currencySymbol }}0.00</strong>
        </div>

        <button type="button" class="icon-button remove-line" data-remove-line aria-label="Remove line" title="Remove line">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
    </div>
</template>
@endsection
