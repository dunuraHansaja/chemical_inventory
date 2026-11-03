<?php
    $drawerOpen = $errors->any() || request()->boolean('produce');
    $productsPayload = $predefinedProducts->map(function ($p) {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'code' => $p->item_code,
            'unit' => $p->stock_unit ?: 'L',
            'category' => $p->productCategory?->name ?? $p->category?->name ?? 'Uncategorized',
            'location' => $p->storage_location ?? '',
            'on_hand' => round((float) ($p->lots_sum_quantity_on_hand ?? 0), 2),
            'recipes' => $p->recipes->map(function ($r) {
                return [
                    'material_id' => $r->material_id,
                    'name' => $r->material?->name ?? 'Ingredient',
                    'code' => $r->material?->item_code ?? '',
                    'unit' => $r->material?->stock_unit ?? 'kg',
                    'per_unit_qty' => round((float) $r->quantity, 2),
                    'available_stock' => round((float) ($r->material?->usable_stock ?? 0), 2),
                ];
            })->values()->all(),
        ];
    })->keyBy('id');
?>

<div class="record-production-backdrop {{ $drawerOpen ? 'is-open' : '' }}" id="record-production-backdrop" data-close-record-production></div>

<aside class="record-production-drawer {{ $drawerOpen ? 'is-open' : '' }}"
       id="record-production-drawer"
       aria-labelledby="record-production-title"
       data-products-map="{{ json_encode($productsPayload) }}">
    <div class="record-production-header">
        <div>
            <span class="record-production-eyebrow">{{ $moduleLabel ?? 'PRODUCTION RUN' }}</span>
            <h2 id="record-production-title">Record Production</h2>
            <p>Produce a batch from a predefined product and automatically deduct recipe ingredients.</p>
        </div>
        <button type="button" class="production-panel-close" data-close-record-production aria-label="Close panel">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>

    <form method="POST" action="{{ $produceRoute }}" class="record-production-form" id="record-production-form">
        @csrf
        <div class="record-production-body">
            @if($errors->any())
                <div class="production-errors-box" role="alert">
                    <strong>Unable to record production:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Predefined Product Selection -->
            <div class="production-field-block">
                <div class="production-field-header">
                    <label class="production-label" for="record-product-select">PREDEFINED PRODUCT <span class="required-star">*</span></label>
                    <span class="record-prod-category-pill" id="record-prod-category-pill" hidden></span>
                </div>
                <div class="production-input-icon-wrap">
                    <span class="production-field-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4a2 2 0 0 0 1.8-3l-5-9V3"/>
                            <path d="M8 15h8"/>
                        </svg>
                    </span>
                    <select id="record-product-select" name="product_id" required data-record-product-select>
                        <option value="">Select predefined product…</option>
                        @foreach($predefinedProducts as $item)
                            <option value="{{ $item->id }}" @selected(old('product_id', request('produce_id')) == $item->id)>
                                {{ $item->name }} ({{ $item->item_code }}) — Stock: {{ number_format((float) ($item->lots_sum_quantity_on_hand ?? 0), 2) }} {{ $item->stock_unit }}
                            </option>
                        @endforeach
                    </select>
                    <svg class="production-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                </div>
            </div>

            <!-- Quantity to Produce & Batch Number -->
            <div class="record-production-grid-2">
                <div class="production-field-block">
                    <label class="production-label" for="record-qty-input">QUANTITY TO PRODUCE <span class="required-star">*</span></label>
                    <div class="production-input-icon-wrap">
                        <span class="production-field-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                                <line x1="12" y1="22.08" x2="12" y2="12"/>
                            </svg>
                        </span>
                        <input id="record-qty-input"
                               type="number"
                               name="quantity"
                               step="0.01"
                               min="0.01"
                               value="{{ old('quantity', '1.00') }}"
                               placeholder="1.00"
                               required
                               data-record-qty-input>
                        <span class="record-unit-suffix" id="record-unit-suffix">L</span>
                    </div>
                </div>

                <div class="production-field-block">
                    <label class="production-label" for="record-batch-input">PRODUCTION BATCH NO.</label>
                    <div class="production-input-icon-wrap">
                        <span class="production-field-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                                <line x1="7" y1="7" x2="7.01" y2="7"/>
                            </svg>
                        </span>
                        <input id="record-batch-input"
                               type="text"
                               name="batch_number"
                               value="{{ old('batch_number', $defaultBatchNo) }}"
                               placeholder="{{ $defaultBatchNo }}">
                    </div>
                </div>
            </div>

            <!-- Production Date & Expiry Date -->
            <div class="record-production-grid-2">
                <div class="production-field-block">
                    <label class="production-label" for="record-produced-at">PRODUCTION DATE</label>
                    <input id="record-produced-at"
                           type="date"
                           name="produced_at"
                           value="{{ old('produced_at', today()->toDateString()) }}">
                </div>

                <div class="production-field-block">
                    <label class="production-label" for="record-expiry-date">EXPIRY DATE</label>
                    <input id="record-expiry-date"
                           type="date"
                           name="expiry_date"
                           value="{{ old('expiry_date') }}">
                </div>
            </div>

            <!-- Storage Location & Notes -->
            <div class="record-production-grid-2">
                <div class="production-field-block">
                    <label class="production-label" for="record-storage-location">STORAGE LOCATION</label>
                    <select id="record-storage-location" name="storage_location" data-record-location-select>
                        <option value="">Default / Unassigned</option>
                        @foreach($storageLocations as $loc)
                            <option value="{{ $loc->name }}" @selected(old('storage_location') === $loc->name)>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="production-field-block">
                    <label class="production-label" for="record-notes">BATCH NOTE</label>
                    <input id="record-notes"
                           type="text"
                           name="notes"
                           value="{{ old('notes') }}"
                           placeholder="Optional production note…">
                </div>
            </div>

            <!-- Auto-Calculated Formulation Recipe Ingredients -->
            <div class="record-recipe-section">
                <div class="record-recipe-header">
                    <div>
                        <h3 class="production-section-heading">REQUIRED RECIPE INGREDIENTS</h3>
                        <p class="production-section-subtext">Quantities scale automatically from the predefined 1-unit recipe.</p>
                    </div>
                    <span class="record-recipe-scale-badge" id="record-recipe-scale-badge">× 1.00 unit</span>
                </div>

                <div class="record-recipe-empty" id="record-recipe-empty">
                    Select a predefined product above to load its 1-unit formulation recipe and check raw material stock availability.
                </div>

                <div class="record-recipe-list" id="record-recipe-list" hidden></div>
            </div>
        </div>

        <div class="production-form-actions-bar">
            <button type="button" class="button production-cancel-button" data-close-record-production>Cancel</button>
            <button type="submit" class="button production-submit-button">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20 6 9 17l-5-5"/>
                </svg>
                <span>Complete &amp; Record Production</span>
            </button>
        </div>
    </form>
</aside>

