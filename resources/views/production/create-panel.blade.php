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

        <!-- Product Name -->
        <div class="production-field-block" data-production-name-group>
            <div class="production-field-header">
                <label class="production-label" for="new-product-input">PRODUCT NAME</label>
            </div>
            <div class="production-input-icon-wrap">
                <span class="production-field-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4a2 2 0 0 0 1.8-3l-5-9V3"/>
                        <path d="M8 15h8"/>
                    </svg>
                </span>
                <input id="new-product-input" type="text" name="new_production_name" value="{{ old('new_production_name') }}" placeholder="Enter new product name" required>
            </div>
            <select id="product-made-select" name="product_id" data-product-select style="display:none;" aria-hidden="true" tabindex="-1">
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
            </select>
            @error('new_production_name')<small class="production-field-error">{{ $message }}</small>@enderror
            @error('product_id')<small class="production-field-error">{{ $message }}</small>@enderror
        </div>

        <!-- Product Classification (Finished Good or Semi Finished Good) -->
        <div class="production-field-block" data-goods-type-group>
            <label class="production-label">PRODUCT CLASSIFICATION</label>
            <div class="production-type-checkboxes">
                <label class="production-type-checkbox-card" for="type-finished-good">
                    <input type="checkbox" name="goods_type" value="finished_good" id="type-finished-good" checked>
                    <span>Finished Good</span>
                </label>
                <label class="production-type-checkbox-card" for="type-semi-finished-good">
                    <input type="checkbox" name="goods_type" value="semi_finished_good" id="type-semi-finished-good">
                    <span>Semi Finished Good</span>
                </label>
            </div>
        </div>

        <!-- Units (1 Unit Recipe) -->
        <div class="production-field-block">
            <label class="production-label" for="quantity-made-input">
                UNITS (1 UNIT RECIPE)
            </label>
            <div class="production-input-icon-wrap" style="background: #f8fafc;">
                <span class="production-field-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3 21 8v8l-9 5-9-5V8l9-5Z"/>
                        <path d="m3 8 9 5 9-5M12 13v8"/>
                    </svg>
                </span>
                <input id="quantity-made-input" type="number" name="quantity" min="1" max="1" step="1" value="1" readonly required style="cursor: default;">
            </div>
            <small class="production-field-hint" style="color: #64748b; font-size: 0.8rem; margin-top: 6px; display: block;">Formulation recipe is defined for 1 unit of product.</small>
            @error('quantity')<small class="production-field-error">{{ $message }}</small>@enderror
        </div>
    </div>

    <!-- Formulation Recipe (Ingredients) Section -->
    <div class="production-ingredients-section" aria-labelledby="production-ingredients-title">
        <div class="ingredients-header-block">
            <h3 id="production-ingredients-title">FORMULATION RECIPE (INGREDIENTS)</h3>
            <p class="ingredients-helper-text">Select raw materials/packagings and their required quantities for <strong>1 unit</strong> of this product.</p>
        </div>

        <div class="ingredients-table-header">
            <span class="col-material">Material / Ingredient</span>
            <span class="col-qty">Quantity (for 1 unit)</span>
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
                <span>No materials added yet. Click <strong>+ Add ingredient</strong> below to select materials for 1 unit.</span>
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
                        <input type="number" name="ingredients[{{ $idx }}][quantity]" min="0.01" step="0.01" value="{{ isset($line['quantity']) && $line['quantity'] !== '' ? number_format((float) $line['quantity'], 2, '.', '') : '' }}" placeholder="0.00" required data-ingredient-qty>
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

    <!-- Formulation Recipe Info Banner -->
    <div class="production-notice-pill">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="16" x2="12" y2="12"/>
            <line x1="12" y1="8" x2="12.01" y2="8"/>
        </svg>
        <span><strong>Formulation Recipe (Per 1 Unit):</strong> Saving pre-defines the raw materials and quantities required to produce 1 unit of this product.</span>
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
            <span id="production-submit-label">Save Product &amp; Recipe</span>
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
            <input type="number" data-name="quantity" min="0.01" step="0.01" placeholder="0.00" required data-ingredient-qty>
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
