<form method="POST" action="{{ route('production.store') }}" class="production-card-form" id="production-form">
    @csrf
    <input type="hidden" name="editing_product_id" id="editing-product-id" value="{{ old('editing_product_id') }}">
    <div class="production-panel-body">
        @if($errors->any())
            <div class="production-errors-box" role="alert">
                <strong>Please check the errors below:</strong>
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

    <div class="production-section-content">
        <!-- Product Category Name -->
        <div class="production-field-block" data-product-category-group>
            <div class="production-field-header">
                <label class="production-label" for="production-category">PRODUCT CATEGORY NAME</label>
                <button type="button" class="production-toggle-category-btn" data-toggle-new-category>
                    <span data-toggle-label>{{ old('new_product_category') ? '← Select existing' : '＋ New category' }}</span>
                </button>
            </div>
            <div class="production-input-icon-wrap" data-category-select-wrap style="{{ old('new_product_category') ? 'display:none;' : '' }}">
                <span class="production-field-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                </span>
                <select id="production-category" name="category_id" data-category-filter>
                    <option value="">Select category name</option>
                    @foreach($formCategories as $category)
                        <option value="{{ $category->id }}" data-name="{{ $category->name }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                    <option value="__new__">＋ Type new category...</option>
                </select>
                <svg class="production-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </div>
            <div class="production-input-icon-wrap" data-new-category-wrap style="{{ old('new_product_category') ? '' : 'display:none;' }}">
                <span class="production-field-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                </span>
                <input id="new-category-input" type="text" name="new_product_category" value="{{ old('new_product_category') }}" placeholder="Type new product category (e.g. Hand Wash, Automobile cleanings)">
            </div>
            @error('category_id')<small class="production-field-error">{{ $message }}</small>@enderror
            @error('new_product_category')<small class="production-field-error">{{ $message }}</small>@enderror
        </div>

        <!-- Production Name -->
        <div class="production-field-block" data-production-name-group>
            <div class="production-field-header">
                <label class="production-label" for="product-made-select">PRODUCTION NAME</label>
                <button type="button" class="production-toggle-category-btn" data-toggle-new-product>
                    <span data-toggle-product-label>{{ old('new_production_name') ? '← Select existing' : '＋ New product' }}</span>
                </button>
            </div>
            <div class="production-input-icon-wrap" data-product-select-wrap style="{{ old('new_production_name') ? 'display:none;' : '' }}">
                <span class="production-field-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4a2 2 0 0 0 1.8-3l-5-9V3"/>
                        <path d="M8 15h8"/>
                    </svg>
                </span>
                <select id="product-made-select" name="product_id" data-product-select>
                    <option value="">Select a product</option>
                    @foreach($materials as $material)
                        <option value="{{ $material->id }}"
                                data-category-id="{{ $material->product_category_id }}"
                                data-unit="{{ $material->stock_unit }}"
                                data-code="{{ $material->item_code }}"
                                @selected(old('product_id') == $material->id)>
                            {{ $material->name }} ({{ $material->item_code }})
                        </option>
                    @endforeach
                    <option value="__new__">＋ Type new product name...</option>
                </select>
                <svg class="production-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </div>
            <div class="production-input-icon-wrap" data-new-product-wrap style="{{ old('new_production_name') ? '' : 'display:none;' }}">
                <span class="production-field-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4a2 2 0 0 0 1.8-3l-5-9V3"/>
                        <path d="M8 15h8"/>
                    </svg>
                </span>
                <input id="new-product-input" type="text" name="new_production_name" value="{{ old('new_production_name') }}" placeholder="Type new production name (e.g. Hand Sanitizer, Hand Wash)">
            </div>
            @error('product_id')<small class="production-field-error">{{ $message }}</small>@enderror
            @error('new_production_name')<small class="production-field-error">{{ $message }}</small>@enderror
        </div>

        <!-- Units & Production Batch (2-Column Grid) -->
        <div class="production-grid-2">
            <div class="production-field-block">
                <label class="production-label" for="quantity-made-input">
                    UNITS <span class="production-unit-pill" data-quantity-unit-tag hidden></span>
                </label>
                <div class="production-input-icon-wrap">
                    <span class="production-field-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3 21 8v8l-9 5-9-5V8l9-5Z"/>
                            <path d="m3 8 9 5 9-5M12 13v8"/>
                        </svg>
                    </span>
                    <input id="quantity-made-input" type="number" name="quantity" min="0.001" step="0.001" value="{{ old('quantity') }}" placeholder="Enter units" required>
                </div>
                @error('quantity')<small class="production-field-error">{{ $message }}</small>@enderror
            </div>

            <div class="production-field-block">
                <label class="production-label" for="batch-no-input">PRODUCTION BATCH</label>
                <div class="production-input-icon-wrap">
                    <span class="production-field-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m20.6 13.4-7.2 7.2a2 2 0 0 1-2.8 0l-7.2-7.2a2 2 0 0 1 0-2.8l7.2-7.2A2 2 0 0 1 12.8 3h7a2 2 0 0 1 2 2v7a2 2 0 0 1-1.2 1.4Z"/>
                            <circle cx="16.5" cy="7.5" r="1"/>
                        </svg>
                    </span>
                    <input id="batch-no-input" type="text" name="batch_number" value="{{ old('batch_number', $defaultBatchNo) }}" placeholder="{{ $defaultBatchNo }}">
                </div>
                @error('batch_number')<small class="production-field-error">{{ $message }}</small>@enderror
            </div>
        </div>

        <!-- Expiry Date -->
        <div class="production-field-block">
            <label class="production-label" for="expiry-date-input">EXPIRY DATE</label>
            <div class="production-input-icon-wrap">
                <span class="production-field-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </span>
                <input id="expiry-date-input" type="date" name="expiry_date" value="{{ old('expiry_date') }}" placeholder="mm/dd/yyyy">
            </div>
            @error('expiry_date')<small class="production-field-error">{{ $message }}</small>@enderror
        </div>

        <!-- Note -->
        <div class="production-field-block">
            <label class="production-label" for="note-input">NOTE</label>
            <div class="production-textarea-icon-wrap">
                <span class="production-field-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>
                    </svg>
                </span>
                <textarea id="note-input" name="notes" rows="2" placeholder="Enter any note...">{{ old('notes') }}</textarea>
            </div>
            @error('notes')<small class="production-field-error">{{ $message }}</small>@enderror
        </div>
    </div>

    <!-- Ingredients Used Section -->
    <div class="production-ingredients-section" aria-labelledby="production-ingredients-title">
        <div class="ingredients-header-block">
            <h3 id="production-ingredients-title">INGREDIENTS USED</h3>
            <p class="ingredients-helper-text">Ingredients are consumed earliest expiry first. Expired lots are skipped.</p>
        </div>

        <div class="ingredients-table-header">
            <span class="col-material">Material / Ingredient</span>
            <span class="col-qty">Quantity</span>
            <span class="col-unit">Unit</span>
            <span class="col-actions">Actions</span>
        </div>

        @error('ingredients')<small class="production-field-error production-form-error">{{ $message }}</small>@enderror

        <div class="ingredients-rows-container" id="ingredients-container" data-ingredients-container>
            @php
                $initialIngredients = old('ingredients', []);
            @endphp

            <div class="ingredients-empty-hint" id="ingredients-empty-hint" style="{{ count($initialIngredients) > 0 ? 'display:none;' : '' }}">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>No materials added yet. Click <strong>+ Add ingredient</strong> below to select materials.</span>
            </div>

            @foreach($initialIngredients as $idx => $line)
                <div class="ingredient-item-row" data-ingredient-row>
                    <!-- Material select with flask icon -->
                    <div class="ingredient-select-cell">
                        <span class="ingredient-field-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4a2 2 0 0 0 1.8-3l-5-9V3"/>
                                <path d="M8 15h8"/>
                            </svg>
                        </span>
                        <select name="ingredients[{{ $idx }}][material_id]" class="ingredient-select-field" required data-ingredient-select>
                            <option value="">Select material</option>
                            @foreach($materials as $material)
                                <option value="{{ $material->id }}" data-unit="{{ $material->stock_unit }}" data-stock="{{ (float)($material->usable_stock ?? 0) }}" @selected(($line['material_id'] ?? '') == $material->id)>{{ $material->name }}</option>
                            @endforeach
                        </select>
                        <svg class="production-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </div>

                    <!-- Quantity input -->
                    <div class="ingredient-qty-cell">
                        <input type="number" name="ingredients[{{ $idx }}][quantity]" min="0.001" step="0.001" value="{{ $line['quantity'] ?? '' }}" placeholder="Qty" required data-ingredient-qty>
                    </div>

                    <!-- Unit select -->
                    <div class="ingredient-unit-cell">
                        <select name="ingredients[{{ $idx }}][unit]" class="ingredient-unit-select" data-ingredient-unit>
                            @php($curUnit = $line['unit'] ?? 'kg')
                            <option value="kg" @selected($curUnit == 'kg')>kg</option>
                            <option value="L" @selected($curUnit == 'L')>L</option>
                            <option value="mL" @selected($curUnit == 'mL')>mL</option>
                            <option value="g" @selected($curUnit == 'g')>g</option>
                            <option value="units" @selected($curUnit == 'units')>units</option>
                        </select>
                        <svg class="production-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </div>

                    <!-- Delete button -->
                    <button type="button" class="ingredient-remove-action" data-remove-ingredient aria-label="Remove ingredient">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            <line x1="10" y1="11" x2="10" y2="17"/>
                            <line x1="14" y1="11" x2="14" y2="17"/>
                        </svg>
                    </button>
                </div>
            @endforeach
        </div>

        <button type="button" class="production-add-ingredient-btn" data-add-ingredient>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Add ingredient</span>
        </button>
    </div>

    <!-- Immediate notice banner -->
    <div class="production-notice-pill">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="16" x2="12" y2="12"/>
            <line x1="12" y1="8" x2="12.01" y2="8"/>
        </svg>
        <span><strong>Stock changes are immediate.</strong> Saving consumes ingredient stock and creates a traceable product batch.</span>
    </div>
    </div>

    <!-- Action buttons -->
    <div class="production-form-actions-bar">
        <button type="button" class="button production-cancel-button" id="production-form-cancel-btn">Cancel</button>
        <button class="button production-submit-button" type="submit" id="production-submit-btn">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                <polyline points="17 21 17 13 7 13 7 21"/>
                <polyline points="7 3 7 8 15 8"/>
            </svg>
            <span id="production-submit-label">Record production</span>
        </button>
    </div>
</form>

<template id="ingredient-row-template">
    <div class="ingredient-item-row" data-ingredient-row>
        <div class="ingredient-select-cell">
            <span class="ingredient-field-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4a2 2 0 0 0 1.8-3l-5-9V3"/>
                    <path d="M8 15h8"/>
                </svg>
            </span>
            <select data-name="material_id" class="ingredient-select-field" required data-ingredient-select>
                <option value="">Select material</option>
                @foreach($materials as $material)
                    <option value="{{ $material->id }}" data-unit="{{ $material->stock_unit }}" data-stock="{{ (float)($material->usable_stock ?? 0) }}">{{ $material->name }}</option>
                @endforeach
            </select>
            <svg class="production-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
        </div>
        <div class="ingredient-qty-cell">
            <input type="number" data-name="quantity" min="0.001" step="0.001" placeholder="Qty" required data-ingredient-qty>
        </div>
        <div class="ingredient-unit-cell">
            <select data-name="unit" class="ingredient-unit-select" data-ingredient-unit>
                <option value="kg">kg</option>
                <option value="L">L</option>
                <option value="mL">mL</option>
                <option value="g">g</option>
                <option value="units">units</option>
            </select>
            <svg class="production-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
        </div>
        <button type="button" class="ingredient-remove-action" data-remove-ingredient aria-label="Remove ingredient">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                <line x1="10" y1="11" x2="10" y2="17"/>
                <line x1="14" y1="11" x2="14" y2="17"/>
            </svg>
        </button>
    </div>
</template>
