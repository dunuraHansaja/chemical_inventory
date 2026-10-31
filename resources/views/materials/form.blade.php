@extends('layouts.app')
@php($isProduct = old('type', $material->type ?? request('type')) === 'product' || $material->isProduct())
@section('title', $material->exists ? ($isProduct ? 'Edit product' : 'Edit material') : ($isProduct ? 'Add product' : 'Add material'))
@section('subtitle', 'Maintain accurate identifiers, categorization, safety details and stock thresholds.')
@section('actions')
    <a class="button button-secondary" href="{{ $material->exists ? route('materials.show', $material) : route('materials.index') }}">← Back</a>
@endsection

@section('content')
<style>
    .material-form-card {
        max-width: 980px;
        margin: 0 auto 30px auto;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05), 0 1px 3px rgba(15, 23, 42, 0.03);
        overflow: hidden;
    }
    .material-section {
        padding: 28px 34px;
        border-bottom: 1px solid #f1f5f9;
    }
    .material-section-header {
        margin-bottom: 22px;
    }
    .material-section-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.09em;
        text-transform: uppercase;
        color: #087f70;
        margin-bottom: 4px;
    }
    .material-section-title {
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 4px 0;
        letter-spacing: -0.02em;
    }
    .material-section-desc {
        font-size: 13px;
        color: #64748b;
        margin: 0;
        line-height: 1.45;
    }
    .material-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 18px 20px;
    }
    .col-12 { grid-column: span 12; }
    .col-8  { grid-column: span 8; }
    .col-7  { grid-column: span 7; }
    .col-6  { grid-column: span 6; }
    .col-5  { grid-column: span 5; }
    .col-4  { grid-column: span 4; }
    .col-3  { grid-column: span 3; }

    @media (max-width: 768px) {
        .col-8, .col-7, .col-6, .col-5, .col-4, .col-3 {
            grid-column: span 12;
        }
        .material-section {
            padding: 20px 20px;
        }
        .material-form-actions {
            padding: 16px 20px;
        }
        .material-type-grid {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-label {
        display: inline-flex !important;
        flex-direction: row !important;
        align-items: center !important;
        gap: 4px !important;
        font-size: 12.5px !important;
        font-weight: 600 !important;
        color: #334155 !important;
        margin: 0 !important;
        line-height: 1.2 !important;
    }
    .form-label .required-star {
        color: #ef4444;
        font-weight: 700;
        font-size: 13px;
        line-height: 1;
    }
    .form-label .label-annotation {
        font-size: 11.5px;
        color: #94a3b8;
        font-weight: 400;
        margin-left: 4px;
    }
    .field-control {
        width: 100%;
        min-height: 42px;
        padding: 9px 13px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        color: #0f172a;
        font-size: 13.5px;
        font-weight: 450;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
    }
    .field-control:hover {
        border-color: #94a3b8;
    }
    .field-control:focus {
        outline: none;
        border-color: #087f70;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(8, 127, 112, 0.12);
    }
    .field-control::placeholder {
        color: #94a3b8;
        font-weight: 400;
    }
    select.field-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 16px 16px;
        padding-right: 36px;
        cursor: pointer;
    }
    textarea.field-control {
        min-height: 88px;
        resize: vertical;
        line-height: 1.5;
    }

    /* Active Material Card Box */
    .active-status-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 18px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        cursor: pointer;
        transition: border-color 0.15s ease, background 0.15s ease;
        margin-top: 4px;
    }
    .active-status-card:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .active-status-card input[type="checkbox"] {
        width: 19px;
        height: 19px;
        min-height: 19px;
        accent-color: #087f70;
        cursor: pointer;
        margin: 0;
        flex-shrink: 0;
    }
    .active-status-text {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .active-status-title {
        font-size: 13.5px;
        font-weight: 600;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .active-status-desc {
        font-size: 12px;
        color: #64748b;
        margin: 0;
        line-height: 1.35;
    }

    /* Actions Bar */
    .material-form-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        padding: 18px 34px;
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
    }
</style>

<form method="POST"
      action="{{ $material->exists ? route('materials.update', $material) : route('materials.store') }}"
      class="material-form-card"
      data-confirm="{{ $material->exists ? 'Save changes to this item? The updated details will be reflected in your inventory.' : 'Are you sure you want to add this item? It will be saved to your inventory.' }}"
      data-confirm-title="{{ $material->exists ? 'Save changes?' : 'Add to catalogue?' }}"
      data-confirm-action="{{ $material->exists ? 'Save Changes' : 'Add Item' }}"
      data-confirm-tone="primary">
    @csrf
    @if($material->exists)
        @method('PUT')
    @endif

    {{-- SECTION 1: IDENTIFICATION & TYPE --}}
    <div class="material-section">
        <div class="material-section-header">
            <span class="material-section-eyebrow">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                CLASSIFICATION &amp; IDENTIFICATION
            </span>
            <h2 class="material-section-title">{{ $isProduct ? 'Product details' : 'Material details' }}</h2>
            <p class="material-section-desc">Manage catalogue classification, primary codes, scientific identifiers, and grouping.</p>
        </div>

        <div class="material-grid">
            {{-- Type Radio Selection (12 cols) --}}
            <div class="form-group col-12">
                <label class="form-label">
                    <span>Catalogue classification</span>
                    <span class="required-star">*</span>
                </label>
                <div class="material-type-grid">
                    <label class="material-type-card {{ !$isProduct ? 'active' : '' }}" data-type-card="material">
                        <input type="radio" name="type" value="material" @checked(!$isProduct)>
                        <span style="font-size: 22px;">🧪</span>
                        <div class="card-text">
                            <strong>Raw Material / Chemical</strong>
                            <small>Chemicals, reagents, solvents, and purchased raw ingredients</small>
                        </div>
                    </label>
                    <label class="material-type-card {{ $isProduct ? 'active' : '' }}" data-type-card="product">
                        <input type="radio" name="type" value="product" @checked($isProduct)>
                        <span style="font-size: 22px;">📦</span>
                        <div class="card-text">
                            <strong>Production Product</strong>
                            <small>Manufactured blends, cleaners, and batch production outputs</small>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Item code (4 cols) & Material name (8 cols) --}}
            <div class="form-group col-4">
                <label class="form-label" for="item_code">
                    <span>Item code</span>
                    <span class="required-star">*</span>
                </label>
                <input id="item_code"
                       class="field-control"
                       name="item_code"
                       value="{{ old('item_code', $material->item_code) }}"
                       maxlength="50"
                       placeholder="e.g. MAT-001 or PROD-001"
                       required>
            </div>

            <div class="form-group col-8">
                <label class="form-label" for="material_name">
                    <span>{{ $isProduct ? 'Product name' : 'Material name' }}</span>
                    <span class="required-star">*</span>
                </label>
                <input id="material_name"
                       class="field-control"
                       name="name"
                       value="{{ old('name', $material->name) }}"
                       placeholder="{{ $isProduct ? 'e.g. Hand Sanitizer 500ml, Windscreen Cleaner' : 'e.g. Ethanol, Absolute 99.5%' }}"
                       required>
            </div>

            {{-- Chemical Category (6 cols - for materials) --}}
            <div class="form-group col-6" data-category-group="material" style="{{ $isProduct ? 'display: none;' : '' }}">
                <label class="form-label" for="category_id">
                    <span>Chemical category</span>
                </label>
                <select id="category_id" class="field-control" name="category_id">
                    <option value="">No category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $material->category_id) == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Product Category (6 cols - for products) --}}
            <div class="form-group col-6" data-category-group="product" style="{{ $isProduct ? '' : 'display: none;' }}">
                <label class="form-label" for="product_category_id">
                    <span>Product category</span>
                </label>
                <select id="product_category_id" class="field-control" name="product_category_id">
                    <option value="">No product category</option>
                    @foreach($productCategories ?? [] as $pCategory)
                        <option value="{{ $pCategory->id }}" @selected(old('product_category_id', $material->product_category_id) == $pCategory->id)>
                            {{ $pCategory->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-6" data-cas-group style="{{ $isProduct ? 'display: none;' : '' }}">
                <label class="form-label" for="cas_number">
                    <span>CAS number</span>
                    <span class="label-annotation">(for chemicals)</span>
                </label>
                <input id="cas_number"
                       class="field-control"
                       name="cas_number"
                       value="{{ old('cas_number', $material->cas_number) }}"
                       maxlength="50"
                       placeholder="e.g. 64-17-5">
            </div>

            {{-- Preferred supplier (6 cols) & Storage location (6 cols) --}}
            <div class="form-group col-6" data-supplier-group style="{{ $isProduct ? 'display: none;' : '' }}">
                <label class="form-label" for="preferred_supplier_id">
                    <span>Preferred supplier</span>
                </label>
                <select id="preferred_supplier_id" class="field-control" name="preferred_supplier_id">
                    <option value="">No preferred supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('preferred_supplier_id', $material->preferred_supplier_id) == $supplier->id)>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-6">
                <label class="form-label" for="storage_location">
                    <span>Storage location</span>
                </label>
                <input id="storage_location"
                       class="field-control"
                       name="storage_location"
                       value="{{ old('storage_location', $material->storage_location) }}"
                       maxlength="255"
                       placeholder="e.g. Warehouse A, Shelf 3-B">
            </div>
        </div>
    </div>

    {{-- SECTION 2: INVENTORY & STOCKING --}}
    <div class="material-section">
        <div class="material-section-header">
            <span class="material-section-eyebrow">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                INVENTORY &amp; STOCKING
            </span>
            <h2 class="material-section-title">Units &amp; reorder triggers</h2>
            <p class="material-section-desc">Define standard units of measure, baseline cost, and low-stock alerting thresholds.</p>
        </div>

        <div class="material-grid">
            {{-- Stock unit (4 cols) --}}
            <div class="form-group col-4">
                <label class="form-label" for="stock_unit">
                    <span>Stock unit</span>
                    <span class="required-star">*</span>
                </label>
                <input id="stock_unit"
                       class="field-control"
                       name="stock_unit"
                       value="{{ old('stock_unit', $material->stock_unit ?? 'kg') }}"
                       maxlength="20"
                       placeholder="e.g. kg, L, units, drums"
                       required>
            </div>

            {{-- Reorder level (4 cols) --}}
            <div class="form-group col-4">
                <label class="form-label" for="reorder_level">
                    <span>Reorder level</span>
                    <span class="required-star">*</span>
                </label>
                <input id="reorder_level"
                       class="field-control"
                       type="number"
                       step="0.001"
                       min="0"
                       name="reorder_level"
                       value="{{ old('reorder_level', $material->reorder_level ?? 0) }}"
                       required>
            </div>

            {{-- Cost per unit (4 cols) --}}
            <div class="form-group col-4">
                <label class="form-label" for="cost_per_unit">
                    <span>Cost per unit</span>
                </label>
                <input id="cost_per_unit"
                       class="field-control"
                       type="number"
                       step="0.01"
                       min="0"
                       name="cost_per_unit"
                       value="{{ old('cost_per_unit', $material->cost_per_unit) }}"
                       placeholder="0.00">
            </div>
        </div>
    </div>

    {{-- SECTION 3: SAFETY & NOTES --}}
    <div class="material-section">
        <div class="material-section-header">
            <span class="material-section-eyebrow">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                SAFETY &amp; SPECIFICATIONS
            </span>
            <h2 class="material-section-title">Hazard profile &amp; handling</h2>
            <p class="material-section-desc">Record regulatory hazard classes and special storage precautions.</p>
        </div>

        <div class="material-grid">
            {{-- Hazard classes (12 cols) --}}
            <div class="form-group col-12">
                <label class="form-label" for="hazard_classes">
                    <span>Hazard classes</span>
                    <span class="label-annotation">(comma-separated)</span>
                </label>
                <input id="hazard_classes"
                       class="field-control"
                       name="hazard_classes"
                       value="{{ old('hazard_classes', $hazardClasses) }}"
                       placeholder="e.g. Flammable, Corrosive, Toxic, Oxidizer">
            </div>

            {{-- Handling notes (12 cols) --}}
            <div class="form-group col-12">
                <label class="form-label" for="handling_notes">
                    <span>Handling notes</span>
                </label>
                <textarea id="handling_notes"
                          class="field-control"
                          name="handling_notes"
                          rows="3"
                          placeholder="Special storage guidelines, ventilation, temperature limits, or personal protective equipment (PPE)...">{{ old('handling_notes', $material->handling_notes) }}</textarea>
            </div>

            {{-- Active material toggle card (12 cols) --}}
            <div class="col-12">
                <label class="active-status-card" for="is_active_checkbox">
                    <input id="is_active_checkbox"
                           type="checkbox"
                           name="is_active"
                           value="1"
                           @checked(old('is_active', $material->is_active ?? true))>
                    <div class="active-status-text">
                        <span class="active-status-title">Active in catalogue</span>
                        <p class="active-status-desc">Allow this item to be utilized in purchase orders, formulation recipes, and lot transactions.</p>
                    </div>
                </label>
            </div>
        </div>
    </div>

    {{-- FORM ACTIONS --}}
    <div class="material-form-actions">
        <a class="button button-secondary" href="{{ $material->exists ? route('materials.show', $material) : route('materials.index') }}">
            Cancel
        </a>
        <button class="button button-primary" type="submit">
            {{ $material->exists ? 'Save changes' : 'Create item' }}
            <span>→</span>
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeCards = document.querySelectorAll('[data-type-card]');
    const rawCatGroup = document.querySelector('[data-category-group="material"]');
    const prodCatGroup = document.querySelector('[data-category-group="product"]');
    const casGroup = document.querySelector('[data-cas-group]');
    const supplierGroup = document.querySelector('[data-supplier-group]');

    typeCards.forEach(card => {
        const radio = card.querySelector('input[type="radio"]');
        card.addEventListener('click', function() {
            typeCards.forEach(c => c.classList.remove('active'));
            card.classList.add('active');
            if (radio) {
                radio.checked = true;
                if (radio.value === 'product') {
                    if (rawCatGroup) rawCatGroup.style.display = 'none';
                    if (prodCatGroup) prodCatGroup.style.display = '';
                    if (casGroup) casGroup.style.display = 'none';
                    if (supplierGroup) supplierGroup.style.display = 'none';
                } else {
                    if (rawCatGroup) rawCatGroup.style.display = '';
                    if (prodCatGroup) prodCatGroup.style.display = 'none';
                    if (casGroup) casGroup.style.display = '';
                    if (supplierGroup) supplierGroup.style.display = '';
                }
            }
        });
    });
});
</script>
@endsection
