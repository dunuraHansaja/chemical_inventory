@extends('layouts.app')
@php($isProduct = old('type', $material->type ?? request('type')) === 'product' || $material->isProduct())
@section('title', $material->exists ? ($isProduct ? 'Edit packaging / can' : 'Edit raw material') : ($isProduct ? 'Add packaging / can' : 'Add raw material'))
@section('eyebrow', 'RAW MATERIALS & PACKAGINGS')
@section('subtitle', $material->exists ? "Update catalogue entry {$material->item_code}" : 'Register a new raw chemical material or packaging item into catalogue.')

@section('content')
<style>
    /* Professional Card Layout */
    .material-form-card {
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
        min-height: 84px;
        resize: vertical;
        line-height: 1.5;
    }

    /* Type Selector Cards */
    .material-type-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .material-type-card,
    .app-shell .material-type-card {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: flex-start !important;
        text-align: left !important;
        gap: 14px !important;
        padding: 16px 20px !important;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 12px !important;
        background: #f8fafc !important;
        cursor: pointer;
        transition: all 0.18s ease;
    }
    .material-type-card:hover,
    .app-shell .material-type-card:hover {
        border-color: #cbd5e1 !important;
        background: #f1f5f9 !important;
    }
    .material-type-card.active,
    .app-shell .material-type-card.active,
    .material-type-card:has(input:checked) {
        border-color: #087f70 !important;
        background: #f0fdfa !important;
        box-shadow: 0 0 0 1px #087f70, 0 4px 12px rgba(8, 127, 112, 0.08) !important;
    }
    .material-type-card input[type="radio"] {
        accent-color: #087f70;
        width: 18px !important;
        height: 18px !important;
        min-height: 18px !important;
        margin: 0 !important;
        flex-shrink: 0;
        cursor: pointer;
    }
    .material-type-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #087f70;
        flex-shrink: 0;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .material-type-card.active .material-type-icon {
        background: #ccfbf1;
        border-color: #99f6e4;
        color: #0f766e;
    }
    .material-type-card .card-text {
        display: flex;
        flex-direction: column;
        gap: 3px;
        min-width: 0;
    }
    .material-type-card .card-text strong {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.25;
    }
    .material-type-card .card-text small {
        font-size: 12px;
        color: #64748b;
        line-height: 1.35;
        margin: 0;
    }

    /* Hazard Classes Multi-Select Icon Grid */
    .hazard-selector-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 4px;
    }
    .hazard-selector-actions {
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    .hazard-selected-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 600;
        color: #475569;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        transition: all 0.15s ease;
    }
    .hazard-selected-badge.has-selection {
        color: #0f766e;
        background: #ccfbf1;
        border-color: #99f6e4;
    }
    .hazard-clear-btn {
        background: none;
        border: none;
        padding: 2px 6px;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        border-radius: 5px;
        transition: color 0.15s ease, background-color 0.15s ease;
    }
    .hazard-clear-btn:hover {
        color: #dc2626;
        background: #fef2f2;
    }
    .hazard-classes-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-top: 4px;
    }
    @media (max-width: 960px) {
        .hazard-classes-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 600px) {
        .hazard-classes-grid {
            grid-template-columns: 1fr;
        }
    }
    .hazard-class-card,
    .app-shell .hazard-class-card {
        position: relative;
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: flex-start !important;
        text-align: left !important;
        gap: 12px !important;
        padding: 12px 14px !important;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 11px !important;
        background: #ffffff !important;
        cursor: pointer;
        user-select: none;
        transition: all 0.16s ease;
    }
    .hazard-class-card:hover,
    .app-shell .hazard-class-card:hover {
        border-color: #cbd5e1 !important;
        background: #f8fafc !important;
        transform: translateY(-1px);
        box-shadow: 0 3px 10px rgba(15, 23, 42, 0.05);
    }
    .hazard-class-card.is-selected,
    .app-shell .hazard-class-card.is-selected {
        border-color: var(--hazard-accent, #087f70) !important;
        background: var(--hazard-bg, #f0fdfa) !important;
        box-shadow: 0 0 0 1px var(--hazard-accent, #087f70), 0 4px 12px rgba(15, 23, 42, 0.06) !important;
    }
    .hazard-class-checkbox {
        display: none !important;
    }
    .hazard-icon-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--hazard-icon-bg, #f1f5f9);
        color: var(--hazard-accent, #475569);
        border: 1px solid var(--hazard-border, #e2e8f0);
        flex-shrink: 0;
        transition: transform 0.16s ease, background-color 0.16s ease;
    }
    .hazard-class-card.is-selected .hazard-icon-badge {
        transform: scale(1.04);
        background: #ffffff;
        border-color: var(--hazard-accent, #087f70);
    }
    .hazard-icon-badge svg {
        width: 21px;
        height: 21px;
    }
    .hazard-card-body {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
        flex: 1;
    }
    .hazard-card-title {
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.25;
    }
    .hazard-card-desc {
        font-size: 11px;
        font-weight: 450;
        color: #64748b;
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .hazard-check-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 6px;
        border: 1.5px solid #cbd5e1;
        background: #ffffff;
        color: transparent;
        flex-shrink: 0;
        transition: all 0.15s ease;
    }
    .hazard-class-card.is-selected .hazard-check-indicator {
        background: var(--hazard-accent, #087f70);
        border-color: var(--hazard-accent, #087f70);
        color: #ffffff;
    }
    .hazard-check-indicator svg {
        width: 13px;
        height: 13px;
    }

    /* Active Status Toggle Card */
    .active-status-card,
    .app-shell .active-status-card {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: flex-start !important;
        text-align: left !important;
        gap: 14px !important;
        padding: 16px 20px !important;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 12px !important;
        background: #f8fafc !important;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .active-status-card:hover,
    .app-shell .active-status-card:hover {
        background: #f1f5f9 !important;
        border-color: #cbd5e1 !important;
    }
    .active-status-card:has(input:checked),
    .app-shell .active-status-card:has(input:checked) {
        border-color: #087f70 !important;
        background: #f0fdfa !important;
    }
    .active-status-card input[type="checkbox"] {
        accent-color: #087f70;
        width: 18px !important;
        height: 18px !important;
        min-height: 18px !important;
        cursor: pointer;
        margin: 0 !important;
        flex-shrink: 0;
    }
    .active-status-text {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .active-status-title {
        font-size: 13.5px;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .active-status-desc {
        font-size: 12px;
        color: #64748b;
        margin: 0;
        line-height: 1.35;
        font-weight: 450;
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
    /* Visibility based on selected catalogue classification */
    .material-form-card[data-active-type="product"] [data-material-only],
    .material-form-card[data-active-type="product"] #section_safety,
    .material-form-card[data-active-type="product"] #hazard_classes_group,
    .material-form-card[data-active-type="product"] #reorder_level_mat_group {
        display: none !important;
    }
    .material-form-card[data-active-type="material"] [data-product-only] {
        display: none !important;
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
            <h2 class="material-section-title">{{ $isProduct ? 'Packaging / Can details' : 'Material details' }}</h2>
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
                        <span class="material-type-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4a2 2 0 0 0 1.8-3l-5-9V3"/><path d="M8 15h8"/></svg>
                        </span>
                        <div class="card-text">
                            <strong>Raw Material / Chemical</strong>
                            <small>Chemicals, reagents, solvents, and purchased raw ingredients</small>
                        </div>
                    </label>
                    <label class="material-type-card {{ $isProduct ? 'active' : '' }}" data-type-card="product">
                        <input type="radio" name="type" value="product" @checked($isProduct)>
                        <span class="material-type-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                        </span>
                        <div class="card-text">
                            <strong>Packagings and Cans</strong>
                            <small>Bottles, drums, cans, cartons, labels, and packaging materials</small>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Item code (4 cols) & Name (8 cols) --}}
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
                       placeholder="e.g. MAT-001 or PKG-001"
                       required>
            </div>

            <div class="form-group col-8">
                <label class="form-label" for="material_name">
                    <span id="name_label">{{ $isProduct ? 'Packaging / Can name' : 'Material name' }}</span>
                    <span class="required-star">*</span>
                </label>
                <input id="material_name"
                       class="field-control"
                       name="name"
                       value="{{ old('name', $material->name) }}"
                       placeholder="{{ $isProduct ? 'e.g. 500ml HDPE Bottle, 20L Metal Can, Cardboard Box' : 'e.g. Ethanol, Absolute 99.5%' }}"
                       required>
            </div>

            {{-- FIELDS FOR RAW MATERIAL / CHEMICAL --}}
            <div class="form-group col-6" data-material-only style="{{ $isProduct ? 'display: none;' : '' }}">
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

            <div class="form-group col-6" data-material-only style="{{ $isProduct ? 'display: none;' : '' }}">
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

            {{-- FIELDS SPECIFIC TO PACKAGINGS AND CANS --}}
            <div class="form-group col-4" data-product-only style="{{ $isProduct ? '' : 'display: none;' }}">
                <label class="form-label" for="packaging_type">
                    <span>Packaging Type</span>
                    <span class="required-star">*</span>
                    <span class="label-annotation">(Box, Can, Bottle etc.)</span>
                </label>
                <select id="packaging_type" class="field-control" name="packaging_type">
                    <option value="">Select packaging type...</option>
                    @foreach(['Box', 'Can', 'Bottle', 'Drum', 'Carton', 'Pail / Bucket', 'Jar', 'Jerry Can', 'Cap / Closure', 'Trigger / Spray Pump', 'Label', 'Other'] as $pType)
                        <option value="{{ $pType }}" @selected(old('packaging_type', $material->packaging_type) === $pType)>{{ $pType }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-4" data-product-only style="{{ $isProduct ? '' : 'display: none;' }}">
                <label class="form-label" for="product_category_id">
                    <span>Packaging category</span>
                </label>
                <select id="product_category_id" class="field-control" name="product_category_id">
                    <option value="">No packaging category</option>
                    @foreach($productCategories ?? [] as $pCategory)
                        <option value="{{ $pCategory->id }}" @selected(old('product_category_id', $material->product_category_id) == $pCategory->id)>
                            {{ $pCategory->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-4" data-product-only style="{{ $isProduct ? '' : 'display: none;' }}">
                <label class="form-label" for="reorder_level_pkg">
                    <span>Reorder Remaining Quantity</span>
                    <span class="required-star">*</span>
                </label>
                <input id="reorder_level_pkg"
                       class="field-control"
                       type="number"
                       step="0.01"
                       min="0"
                       name="reorder_level"
                       value="{{ old('reorder_level', $material->exists ? number_format((float) $material->reorder_level, 2, '.', '') : '0.00') }}"
                       placeholder="0.00"
                       {{ $isProduct ? 'required' : 'disabled' }}>
            </div>

            {{-- COMMON FIELDS: Supplier & Storage location --}}
            <div class="form-group col-6" data-supplier-group>
                <label class="form-label" for="preferred_supplier_id">
                    <span id="supplier_label">{{ $isProduct ? 'Supplier' : 'Preferred supplier' }}</span>
                </label>
                <select id="preferred_supplier_id" class="field-control" name="preferred_supplier_id">
                    <option value="">No supplier assigned</option>
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
                <select id="storage_location" class="field-control" name="storage_location">
                    <option value="">No storage location</option>
                    @foreach($storageLocations as $location)
                        <option value="{{ $location->name }}" @selected(old('storage_location', $material->storage_location) == $location->name)>
                            {{ $location->name }}@if($location->address) — {{ $location->address }}@endif
                        </option>
                    @endforeach
                    @if($material->storage_location && ! $storageLocations->contains('name', $material->storage_location))
                        <option value="{{ $material->storage_location }}" selected>{{ $material->storage_location }} (Legacy / Other)</option>
                    @endif
                </select>
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
            {{-- Stock unit (4 cols or 6 cols for product) --}}
            <div class="form-group {{ $isProduct ? 'col-4' : 'col-3' }}" id="stock_unit_group">
                <label class="form-label" for="stock_unit">
                    <span>Stock unit</span>
                    <span class="required-star">*</span>
                </label>
                <input id="stock_unit"
                       class="field-control"
                       name="stock_unit"
                       value="{{ old('stock_unit', $material->stock_unit ?? ($isProduct ? 'units' : 'kg')) }}"
                       maxlength="20"
                       placeholder="e.g. units, pcs, boxes, cans, bottles, kg, L"
                       required>
            </div>

            {{-- Reorder level (for Raw Material) --}}
            {{-- Initial purchase quantity (col-3 for material, col-4 for product) --}}
            <div class="form-group {{ $isProduct ? 'col-4' : 'col-3' }}" id="initial_quantity_group">
                <label class="form-label" for="initial_purchase_quantity">
                    <span>Initial purchase quantity</span>
                    <span class="label-annotation">(Opening stock)</span>
                </label>
                <input id="initial_purchase_quantity"
                       class="field-control"
                       type="number"
                       step="0.01"
                       min="0"
                       name="initial_purchase_quantity"
                       value="{{ old('initial_purchase_quantity', isset($initialPurchaseQuantity) && $initialPurchaseQuantity !== null ? number_format((float) $initialPurchaseQuantity, 2, '.', '') : '0.00') }}"
                       placeholder="0.00">
            </div>

            {{-- Reorder level (for Raw Material) --}}
            <div class="form-group col-3" id="reorder_level_mat_group" style="{{ $isProduct ? 'display: none !important;' : '' }}">
                <label class="form-label" for="reorder_level_mat">
                    <span>Reorder level</span>
                    <span class="required-star">*</span>
                </label>
                <input id="reorder_level_mat"
                       class="field-control"
                       type="number"
                       step="0.01"
                       min="0"
                       name="reorder_level"
                       value="{{ old('reorder_level', $material->exists ? number_format((float) $material->reorder_level, 2, '.', '') : '0.00') }}"
                       placeholder="0.00"
                       {{ $isProduct ? 'disabled' : 'required' }}>
            </div>

            {{-- Cost per unit (4 cols or 6 cols for product) --}}
            <div class="form-group {{ $isProduct ? 'col-4' : 'col-3' }}" id="cost_per_unit_group">
                <label class="form-label" for="cost_per_unit">
                    <span>Cost per unit</span>
                </label>
                <input id="cost_per_unit"
                       class="field-control"
                       type="number"
                       step="0.01"
                       min="0"
                       name="cost_per_unit"
                       value="{{ old('cost_per_unit', $material->cost_per_unit !== null ? number_format((float) $material->cost_per_unit, 2, '.', '') : '') }}"
                       placeholder="0.00">
            </div>
        </div>
    </div>

    <input type="hidden" id="is_active_fallback" name="is_active" value="1" {{ $isProduct ? '' : 'disabled' }}>

    {{-- SECTION 3: SAFETY & NOTES (Raw Materials / Chemicals only) --}}
    <div class="material-section" id="section_safety" data-material-only style="{{ $isProduct ? 'display: none !important;' : '' }}">
        <div class="material-section-header">
            <span class="material-section-eyebrow">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                SAFETY &amp; SPECIFICATIONS
            </span>
            <h2 class="material-section-title">Hazard profile &amp; handling</h2>
            <p class="material-section-desc">Record regulatory hazard classes and special storage precautions.</p>
        </div>

        <div class="material-grid">
            {{-- Hazard classes multi-select icons (12 cols) --}}
            <?php
                $rawHazards = old('hazard_classes', $hazardClasses);
                $selectedHazardsList = collect(is_array($rawHazards) ? $rawHazards : explode(',', (string) $rawHazards))
                    ->map(fn ($h) => trim((string) $h))
                    ->filter()
                    ->values()
                    ->all();
                $selectedHazardsLower = array_map('strtolower', $selectedHazardsList);

                $hazardCatalog = [
                    [
                        'name' => 'Flammable',
                        'desc' => 'Ignitable liquids, gases & solids',
                        'accent' => '#ef4444',
                        'bg' => '#fef2f2',
                        'icon_bg' => '#fee2e2',
                        'border' => '#fecaca',
                        'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>',
                    ],
                    [
                        'name' => 'Corrosive',
                        'desc' => 'Burns skin, eyes & metals',
                        'accent' => '#ea580c',
                        'bg' => '#fff7ed',
                        'icon_bg' => '#ffedd5',
                        'border' => '#fed7aa',
                        'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 2v7.31"/><path d="M14 9.3V1.99"/><path d="M8.5 2h7"/><path d="M14 9.3a6.5 6.5 0 1 1-4 0"/><path d="M5.52 16h12.96"/></svg>',
                    ],
                    [
                        'name' => 'Toxic',
                        'desc' => 'Acute poison or fatal hazard',
                        'accent' => '#dc2626',
                        'bg' => '#fef2f2',
                        'icon_bg' => '#fee2e2',
                        'border' => '#fecaca',
                        'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"/><circle cx="15" cy="12" r="1"/><path d="M8 20v2h8v-2"/><path d="m12.5 17-.5-1-.5 1h1z"/><path d="M16 20a2 2 0 0 0 1.56-3.25 8 8 0 1 0-11.12 0A2 2 0 0 0 8 20"/></svg>',
                    ],
                    [
                        'name' => 'Oxidizer',
                        'desc' => 'Intensifies fire & combustion',
                        'accent' => '#d97706',
                        'bg' => '#fffbeb',
                        'icon_bg' => '#fef3c7',
                        'border' => '#fde68a',
                        'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="16" r="5"/><path d="M12 11c-1.5-2-2.5-4-1-7 1.5 1.5 3 3 3 5 1-1 1.5-2 1.5-3.5 1.5 2 1.5 4 0 5.5"/></svg>',
                    ],
                    [
                        'name' => 'Explosive',
                        'desc' => 'Unstable or self-reactive',
                        'accent' => '#e11d48',
                        'bg' => '#fff1f2',
                        'icon_bg' => '#ffe4e6',
                        'border' => '#fecdd3',
                        'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
                    ],
                    [
                        'name' => 'Compressed Gas',
                        'desc' => 'Pressurized or liquefied gas',
                        'accent' => '#0284c7',
                        'bg' => '#f0f9ff',
                        'icon_bg' => '#e0f2fe',
                        'border' => '#bae6fd',
                        'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="6" width="8" height="15" rx="3"/><path d="M10 6V4a2 2 0 0 1 4 0v2"/><path d="M12 2v2"/><path d="M8 12h8"/></svg>',
                    ],
                    [
                        'name' => 'Health Hazard',
                        'desc' => 'Respiratory or organ toxicity',
                        'accent' => '#7c3aed',
                        'bg' => '#f5f3ff',
                        'icon_bg' => '#ede9fe',
                        'border' => '#ddd6fe',
                        'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/><path d="M12 8v6"/><path d="M9 11h6"/></svg>',
                    ],
                    [
                        'name' => 'Irritant',
                        'desc' => 'Skin, eye or airway irritant',
                        'accent' => '#ca8a04',
                        'bg' => '#fefce8',
                        'icon_bg' => '#fef9c3',
                        'border' => '#fef08a',
                        'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
                    ],
                    [
                        'name' => 'Environmental',
                        'desc' => 'Hazardous to aquatic life',
                        'accent' => '#059669',
                        'bg' => '#ecfdf5',
                        'icon_bg' => '#d1fae5',
                        'border' => '#a7f3d0',
                        'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>',
                    ],
                ];

                $presetLowerNames = array_map(fn ($item) => strtolower($item['name']), $hazardCatalog);
                foreach ($selectedHazardsList as $existingHazard) {
                    if (!in_array(strtolower($existingHazard), $presetLowerNames, true)) {
                        $hazardCatalog[] = [
                            'name' => $existingHazard,
                            'desc' => 'Custom regulatory hazard class',
                            'accent' => '#087f70',
                            'bg' => '#f0fdfa',
                            'icon_bg' => '#ccfbf1',
                            'border' => '#99f6e4',
                            'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
                        ];
                    }
                }
            ?>

            <div class="form-group col-12" id="hazard_classes_group" style="{{ $isProduct ? 'display: none;' : '' }}">
                <div class="hazard-selector-header">
                    <label class="form-label" for="hazard_classes">
                        <span>Hazard classes</span>
                        <span class="label-annotation">(Select one or multiple hazard indicators)</span>
                    </label>
                    <div class="hazard-selector-actions">
                        <span class="hazard-selected-badge {{ count($selectedHazardsList) > 0 ? 'has-selection' : '' }}" id="hazard_selected_count">
                            {{ count($selectedHazardsList) }} selected
                        </span>
                        <button type="button" class="hazard-clear-btn" id="hazard_clear_btn" style="{{ count($selectedHazardsList) > 0 ? '' : 'display: none;' }}">
                            Clear all
                        </button>
                    </div>
                </div>

                <input type="hidden"
                       id="hazard_classes"
                       name="hazard_classes"
                       value="{{ implode(', ', $selectedHazardsList) }}">

                <div class="hazard-classes-grid" role="group" aria-label="Hazard classes">
                    @foreach($hazardCatalog as $hazard)
                        @php($isSelected = in_array(strtolower($hazard['name']), $selectedHazardsLower, true))
                        <div class="hazard-class-card {{ $isSelected ? 'is-selected' : '' }}"
                             data-hazard-card
                             data-hazard-name="{{ $hazard['name'] }}"
                             role="button"
                             tabindex="0"
                             style="--hazard-accent: {{ $hazard['accent'] }}; --hazard-bg: {{ $hazard['bg'] }}; --hazard-icon-bg: {{ $hazard['icon_bg'] }}; --hazard-border: {{ $hazard['border'] }};">
                            <input type="checkbox"
                                   class="hazard-class-checkbox"
                                   data-hazard-checkbox
                                   value="{{ $hazard['name'] }}"
                                   @checked($isSelected)
                                   tabindex="-1"
                                   aria-hidden="true">
                            <span class="hazard-icon-badge" aria-hidden="true">
                                {!! $hazard['svg'] !!}
                            </span>
                            <span class="hazard-card-body">
                                <span class="hazard-card-title">{{ $hazard['name'] }}</span>
                                <span class="hazard-card-desc">{{ $hazard['desc'] }}</span>
                            </span>
                            <span class="hazard-check-indicator" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                        </div>
                    @endforeach
                </div>
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
                          placeholder="Special storage guidelines, stacking instructions, temperature limits, or handling notes...">{{ old('handling_notes', $material->handling_notes) }}</textarea>
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
function initMaterialForm() {
    const formCard = document.querySelector('.material-form-card');
    const typeCards = document.querySelectorAll('[data-type-card]');
    const typeRadios = document.querySelectorAll('input[name="type"]');
    const materialElements = document.querySelectorAll('[data-material-only]');
    const productElements = document.querySelectorAll('[data-product-only]');
    const reorderPkg = document.getElementById('reorder_level_pkg');
    const reorderMat = document.getElementById('reorder_level_mat');
    const reorderMatGroup = document.getElementById('reorder_level_mat_group');
    const stockUnitGroup = document.getElementById('stock_unit_group');
    const costGroup = document.getElementById('cost_per_unit_group');
    const initialQtyGroup = document.getElementById('initial_quantity_group');
    const sectionTitle = document.querySelector('.material-section-title');
    const nameLabel = document.getElementById('name_label');
    const nameInput = document.getElementById('material_name');
    const supplierLabel = document.getElementById('supplier_label');
    const hazardGroup = document.getElementById('hazard_classes_group');
    const stockUnitInput = document.getElementById('stock_unit');
    const safetySection = document.getElementById('section_safety');
    const isActiveFallback = document.getElementById('is_active_fallback');
    const isActiveCheckbox = document.getElementById('is_active_checkbox');

    function syncType(type) {
        if (formCard) {
            formCard.setAttribute('data-active-type', type);
        }

        if (type === 'product') {
            materialElements.forEach(el => el.style.setProperty('display', 'none', 'important'));
            productElements.forEach(el => el.style.display = '');
            if (reorderMatGroup) reorderMatGroup.style.setProperty('display', 'none', 'important');
            if (stockUnitGroup) stockUnitGroup.className = 'form-group col-4';
            if (initialQtyGroup) initialQtyGroup.className = 'form-group col-4';
            if (costGroup) costGroup.className = 'form-group col-4';
            if (reorderPkg) { reorderPkg.disabled = false; reorderPkg.required = true; }
            if (reorderMat) { reorderMat.disabled = true; reorderMat.required = false; }
            if (sectionTitle) sectionTitle.textContent = 'Packaging / Can details';
            if (nameLabel) nameLabel.textContent = 'Packaging / Can name';
            if (nameInput) nameInput.placeholder = 'e.g. 500ml HDPE Bottle, 20L Metal Can, Cardboard Box';
            if (supplierLabel) supplierLabel.textContent = 'Supplier';
            if (hazardGroup) hazardGroup.style.setProperty('display', 'none', 'important');
            if (safetySection) safetySection.style.setProperty('display', 'none', 'important');
            if (isActiveFallback) isActiveFallback.disabled = false;
            if (isActiveCheckbox) isActiveCheckbox.disabled = true;
            if (stockUnitInput && (!stockUnitInput.value || stockUnitInput.value === 'kg')) {
                stockUnitInput.value = 'units';
            }
        } else {
            materialElements.forEach(el => el.style.display = '');
            productElements.forEach(el => el.style.setProperty('display', 'none', 'important'));
            if (reorderMatGroup) reorderMatGroup.style.display = '');
            if (stockUnitGroup) stockUnitGroup.className = 'form-group col-3';
            if (initialQtyGroup) initialQtyGroup.className = 'form-group col-3';
            if (reorderMatGroup) reorderMatGroup.className = 'form-group col-3';
            if (costGroup) costGroup.className = 'form-group col-3';
            if (reorderPkg) { reorderPkg.disabled = true; reorderPkg.required = false; }
            if (reorderMat) { reorderMat.disabled = false; reorderMat.required = true; }
            if (sectionTitle) sectionTitle.textContent = 'Material details';
            if (nameLabel) nameLabel.textContent = 'Material name';
            if (nameInput) nameInput.placeholder = 'e.g. Ethanol, Absolute 99.5%';
            if (supplierLabel) supplierLabel.textContent = 'Preferred supplier';
            if (hazardGroup) hazardGroup.style.display = '';
            if (safetySection) safetySection.style.display = '';
            if (isActiveFallback) isActiveFallback.disabled = true;
            if (isActiveCheckbox) isActiveCheckbox.disabled = false;
            if (stockUnitInput && stockUnitInput.value === 'units') {
                stockUnitInput.value = 'kg';
            }
        }
    }

    typeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            typeCards.forEach(c => {
                const r = c.querySelector('input[type="radio"]');
                c.classList.toggle('active', r && r.checked);
            });
            syncType(this.value);
        });
    });

    typeCards.forEach(card => {
        card.addEventListener('click', function() {
            const radio = card.querySelector('input[type="radio"]');
            if (radio && !radio.checked) {
                radio.checked = true;
                typeCards.forEach(c => c.classList.remove('active'));
                card.classList.add('active');
                syncType(radio.value);
            }
        });
    });

    const initialChecked = document.querySelector('input[name="type"]:checked');
    if (initialChecked) {
        syncType(initialChecked.value);
    }

    if (reorderPkg && reorderMat) {
        reorderPkg.addEventListener('input', function() {
            reorderMat.value = reorderPkg.value;
        });
        reorderMat.addEventListener('input', function() {
            reorderPkg.value = reorderMat.value;
        });
    }

    // Multi-select Hazard Classes
    const hazardHiddenInput = document.getElementById('hazard_classes');
    const hazardCountBadge = document.getElementById('hazard_selected_count');
    const hazardClearBtn = document.getElementById('hazard_clear_btn');
    const hazardGrid = document.querySelector('.hazard-classes-grid');

    function syncHazardClasses() {
        const selected = [];
        document.querySelectorAll('[data-hazard-card]').forEach(card => {
            const isSel = card.classList.contains('is-selected');
            const name = card.getAttribute('data-hazard-name');
            const cb = card.querySelector('[data-hazard-checkbox]');
            if (cb) {
                cb.checked = isSel;
            }
            if (isSel && name) {
                selected.push(name);
            }
        });

        if (hazardHiddenInput) {
            hazardHiddenInput.value = selected.join(', ');
        }
        if (hazardCountBadge) {
            hazardCountBadge.textContent = selected.length + ' selected';
            hazardCountBadge.classList.toggle('has-selection', selected.length > 0);
        }
        if (hazardClearBtn) {
            hazardClearBtn.style.display = selected.length > 0 ? '' : 'none';
        }
    }

    if (hazardGrid) {
        hazardGrid.addEventListener('click', function(e) {
            const card = e.target.closest('[data-hazard-card]');
            if (!card) return;
            e.preventDefault();
            card.classList.toggle('is-selected');
            syncHazardClasses();
        });

        hazardGrid.addEventListener('keydown', function(e) {
            if (e.key === ' ' || e.key === 'Enter') {
                const card = e.target.closest('[data-hazard-card]');
                if (!card) return;
                e.preventDefault();
                card.classList.toggle('is-selected');
                syncHazardClasses();
            }
        });
    }

    if (hazardClearBtn) {
        hazardClearBtn.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('[data-hazard-card]').forEach(c => {
                c.classList.remove('is-selected');
            });
            syncHazardClasses();
        });
    }

    syncHazardClasses();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMaterialForm);
} else {
    initMaterialForm();
}
</script>
@endsection
