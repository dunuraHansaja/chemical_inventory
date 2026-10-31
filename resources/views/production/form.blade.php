@extends('layouts.app')
@section('title', 'Record production')
@section('subtitle', 'Inputs are consumed from unexpired lots using FEFO; output creates a new traceable lot.')
@section('actions')<a class="button button-secondary" href="{{ route('production.index') }}">← Back to production</a>@endsection

@section('content')
<form method="POST" action="{{ route('production.store') }}" class="production-card-form" id="production-form">
    @csrf

    <div class="production-form-header">
        <span class="eyebrow">PRODUCTION RUN</span>
        <h2>Record production</h2>
        <p class="production-form-subtitle">Inputs are consumed from unexpired lots using FEFO order; output creates a new traceable batch lot.</p>
    </div>

    @if($errors->any())
        <div class="production-errors-box" role="alert">
            <strong>Please check the errors below:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="production-section-content">
        <!-- Product Category -->
        <div class="production-field-block" data-product-category-group>
            <div class="production-field-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                <label class="production-label" for="production-category" style="margin-bottom:0;">Product Category</label>
                <button type="button" class="production-toggle-category-btn" data-toggle-new-category type="button">
                    <span data-toggle-label>{{ old('new_product_category') ? '← Select existing' : '＋ New category' }}</span>
                </button>
            </div>
            <div class="production-select-wrap" data-category-select-wrap style="{{ old('new_product_category') ? 'display:none;' : '' }}">
                <select id="production-category" name="category_id" data-category-filter>
                    <option value="">Select product category</option>
                    @foreach($formCategories ?? [] as $category)
                        <option value="{{ $category->id }}" data-name="{{ $category->name }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                    <option value="__new__">＋ Type new category...</option>
                </select>
                <svg class="production-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </div>
            <div class="production-input-wrap" data-new-category-wrap style="{{ old('new_product_category') ? '' : 'display:none;' }}">
                <input id="new-category-input" type="text" name="new_product_category" value="{{ old('new_product_category') }}" placeholder="Type new product category (e.g. Hand Wash, Automobile cleanings)">
            </div>
            @error('category_id')<small class="production-field-error">{{ $message }}</small>@enderror
            @error('new_product_category')<small class="production-field-error">{{ $message }}</small>@enderror
        </div>

        <!-- Product made (full width) -->
        <div class="production-field-block">
            <label class="production-label" for="product-made-select">
                Product made <span class="required">*</span>
            </label>
            <div class="production-select-wrap">
                <select id="product-made-select" name="product_id" required data-product-select>
                    <option value="">Choose...</option>
                    @foreach($materials as $material)
                        <option value="{{ $material->id }}"
                                data-unit="{{ $material->stock_unit }}"
                                data-code="{{ $material->item_code }}"
                                @selected(old('product_id') == $material->id)>
                            {{ $material->item_code }} — {{ $material->name }}
                        </option>
                    @endforeach
                </select>
                <svg class="production-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </div>
            @error('product_id')<small class="production-field-error">{{ $message }}</small>@enderror
        </div>

        <!-- Row 2: Units & Production batch no. -->
        <div class="production-grid-2">
            <div class="production-field-block">
                <label class="production-label" for="quantity-made-input">
                    Units <span class="production-unit-pill" data-quantity-unit-tag hidden></span> <span class="required">*</span>
                </label>
                <div class="production-input-wrap">
                    <input id="quantity-made-input" type="number" name="quantity" min="0.001" step="0.001" value="{{ old('quantity') }}" placeholder="e.g. 20" required>
                </div>
                @error('quantity')<small class="production-field-error">{{ $message }}</small>@enderror
            </div>

            <div class="production-field-block">
                <label class="production-label" for="batch-no-input">
                    Production batch no.
                    <span class="production-helper-text">leave blank for {{ $defaultBatchNo }}</span>
                </label>
                <div class="production-input-wrap">
                    <input id="batch-no-input" type="text" name="batch_number" value="{{ old('batch_number') }}" placeholder="{{ $defaultBatchNo }}">
                </div>
                @error('batch_number')<small class="production-field-error">{{ $message }}</small>@enderror
            </div>
        </div>

        <!-- Row 3: Expiry date & Note -->
        <div class="production-grid-2">
            <div class="production-field-block">
                <label class="production-label" for="expiry-date-input">
                    Expiry date
                </label>
                <div class="production-input-wrap">
                    <input id="expiry-date-input" type="date" name="expiry_date" value="{{ old('expiry_date') }}">
                </div>
                @error('expiry_date')<small class="production-field-error">{{ $message }}</small>@enderror
            </div>

            <div class="production-field-block">
                <label class="production-label" for="note-input">
                    Note
                </label>
                <div class="production-input-wrap">
                    <input id="note-input" type="text" name="notes" value="{{ old('notes') }}" placeholder="Line 1, shift A">
                </div>
                @error('notes')<small class="production-field-error">{{ $message }}</small>@enderror
            </div>
        </div>
    </div>

    <!-- Divider -->
    <hr class="production-section-divider">

    <!-- Ingredients used Section -->
    <div class="production-ingredients-section">
        <div class="ingredients-header-block">
            <h3>Ingredients used</h3>
            <p class="ingredients-helper-text">Taken from stock earliest expiry first. Expired lots are skipped.</p>
        </div>

        @error('ingredients')
            <small class="production-field-error" style="display:block;margin-bottom:12px;">{{ $message }}</small>
        @enderror

        <div class="ingredients-rows-container" id="ingredients-container" data-ingredients-container>
            @php($initialIngredients = old('ingredients', [['material_id' => '', 'quantity' => '']]))
            @foreach($initialIngredients as $idx => $line)
                <div class="ingredient-item-row" data-ingredient-row>
                    <div class="ingredient-select-cell">
                        <select name="ingredients[{{ $idx }}][material_id]" class="ingredient-select-field" required data-ingredient-select>
                            <option value="">Choose ingredient...</option>
                            @foreach($materials as $material)
                                <option value="{{ $material->id }}"
                                        data-unit="{{ $material->stock_unit }}"
                                        data-stock="{{ (float)($material->usable_stock ?? 0) }}"
                                        @selected(($line['material_id'] ?? '') == $material->id)>
                                    {{ $material->item_code }} — {{ $material->name }}
                                </option>
                            @endforeach
                        </select>
                        <svg class="production-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </div>

                    <div class="ingredient-qty-cell">
                        <input type="number" name="ingredients[{{ $idx }}][quantity]" min="0.001" step="0.001" value="{{ $line['quantity'] ?? '' }}" placeholder="Qty" required data-ingredient-qty>
                    </div>

                    <div class="ingredient-usable-cell" data-stock-indicator>
                        <span class="usable-stock-text" data-stock-text>—</span>
                    </div>

                    <button type="button" class="ingredient-remove-action" data-remove-ingredient aria-label="Remove ingredient">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m18 6-12 12M6 6l12 12"/></svg>
                    </button>
                </div>
            @endforeach
        </div>

        <button type="button" class="production-add-ingredient-btn" data-add-ingredient>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            <span>Add ingredient</span>
        </button>
    </div>

    <!-- Notice -->
    <div class="production-notice-pill">
        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
        <div><strong>Stock changes are immediate.</strong> Saving this batch consumes ingredient stock using FEFO and generates the new product batch lot.</div>
    </div>

    <!-- Footer actions -->
    <div class="production-form-actions-bar">
        <a class="button production-cancel-button" href="{{ route('production.index') }}">Cancel</a>
        <button class="button production-submit-button" type="submit">
            <span>Record batch</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
    </div>
</form>

<template id="ingredient-row-template">
    <div class="ingredient-item-row" data-ingredient-row>
        <div class="ingredient-select-cell">
            <select data-name="material_id" class="ingredient-select-field" required data-ingredient-select>
                <option value="">Choose ingredient...</option>
                @foreach($materials as $material)
                    <option value="{{ $material->id }}"
                            data-unit="{{ $material->stock_unit }}"
                            data-stock="{{ (float)($material->usable_stock ?? 0) }}">
                        {{ $material->item_code }} — {{ $material->name }}
                    </option>
                @endforeach
            </select>
            <svg class="production-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
        </div>

        <div class="ingredient-qty-cell">
            <input type="number" data-name="quantity" min="0.001" step="0.001" placeholder="Qty" required data-ingredient-qty>
        </div>

        <div class="ingredient-usable-cell" data-stock-indicator>
            <span class="usable-stock-text" data-stock-text>—</span>
        </div>

        <button type="button" class="ingredient-remove-action" data-remove-ingredient aria-label="Remove ingredient">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m18 6-12 12M6 6l12 12"/></svg>
        </button>
    </div>
</template>
@endsection
