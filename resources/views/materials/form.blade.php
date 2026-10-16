@extends('layouts.app')
@section('title', $material->exists ? 'Edit material' : 'Add material')
@section('subtitle', 'Maintain accurate identifiers, safety details and stock thresholds.')
@section('actions')<a class="button button-secondary" href="{{ $material->exists ? route('materials.show', $material) : route('materials.index') }}">← Back</a>@endsection
@section('content')
    <form method="POST" action="{{ $material->exists ? route('materials.update', $material) : route('materials.store') }}" class="panel form-panel" data-confirm="{{ $material->exists ? 'Save changes to this material? The updated details will be reflected in your inventory.' : 'Are you sure you want to add this material? It will be saved to your inventory.' }}" data-confirm-title="{{ $material->exists ? 'Save material changes?' : 'Add this material?' }}" data-confirm-action="{{ $material->exists ? 'Save Changes' : 'Add Material' }}" data-confirm-tone="primary">
        @csrf @if($material->exists)@method('PUT')@endif
        <div class="form-section"><span class="eyebrow">IDENTIFICATION</span><h2>Material details</h2><div class="form-grid">
            <label>Item code <span class="required">*</span><input name="item_code" value="{{ old('item_code', $material->item_code) }}" maxlength="50" required></label>
            <label>Material name <span class="required">*</span><input name="name" value="{{ old('name', $material->name) }}" required></label>
            <label>CAS number<input name="cas_number" value="{{ old('cas_number', $material->cas_number) }}" maxlength="50" placeholder="e.g. 64-17-5"></label>
            <label>Category<select name="category_id"><option value="">No category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $material->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></label>
            <label>Preferred supplier<select name="preferred_supplier_id"><option value="">No preferred supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(old('preferred_supplier_id', $material->preferred_supplier_id) == $supplier->id)>{{ $supplier->name }}</option>@endforeach</select></label>
            <label>Storage location<input name="storage_location" value="{{ old('storage_location', $material->storage_location) }}" placeholder="e.g. Cabinet A · Shelf 2"></label>
        </div></div>
        <div class="form-section"><span class="eyebrow">STOCK &amp; HANDLING</span><h2>Operational details</h2><div class="form-grid">
            <label>Stock unit <span class="required">*</span><input name="stock_unit" value="{{ old('stock_unit', $material->stock_unit ?? 'kg') }}" maxlength="20" placeholder="kg, L, g, pcs" required></label>
            <label>Reorder level <span class="required">*</span><input type="number" name="reorder_level" min="0" step="0.001" value="{{ old('reorder_level', $material->reorder_level ?? 0) }}" required></label>
            <label>Cost per unit<input type="number" name="cost_per_unit" min="0" step="0.01" value="{{ old('cost_per_unit', $material->cost_per_unit) }}"></label>
            <label>Hazard classes <small>Separate multiple values with commas</small><input name="hazard_classes" value="{{ old('hazard_classes', $hazardClasses) }}" placeholder="Flammable, Corrosive"></label>
            <label class="span-2">Handling notes<textarea name="handling_notes" rows="4">{{ old('handling_notes', $material->handling_notes) }}</textarea></label>
            <label class="checkbox-label"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $material->is_active ?? true))> Active material</label>
        </div></div>
        <div class="form-actions"><a class="button button-secondary" href="{{ $material->exists ? route('materials.show', $material) : route('materials.index') }}">Cancel</a><button class="button button-primary" type="submit">{{ $material->exists ? 'Save changes' : 'Create material' }} <span>→</span></button></div>
    </form>
@endsection
