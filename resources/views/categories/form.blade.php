@extends('layouts.app')
@section('title', $category->exists ? 'Edit category' : 'Add category')
@section('subtitle', 'Category names help keep the materials catalogue organized.')
@section('actions')
    <a class="button button-secondary" href="{{ route('categories.index') }}">← Back to categories</a>
@endsection

@section('content')
<form method="POST" action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}" class="panel form-panel form-narrow" @if($category->exists) data-confirm="Save changes to the category “{{ $category->name }}”?" data-confirm-title="Save category changes?" data-confirm-action="Save Changes" data-confirm-tone="primary" @endif>
    @csrf
    @if($category->exists)
        @method('PUT')
    @endif

    <div class="form-section">
        <span class="eyebrow">CATEGORY DETAILS</span>
        <h2>Category information</h2>
        <p class="form-hint">Category names help keep the materials catalogue organized and easy to filter.</p>

        <div class="form-grid">
            <label class="span-2">Category name <span class="required">*</span>
                <input name="name" value="{{ old('name', $category->name) }}" placeholder="e.g. Solvents, Acids, Packaging" required>
            </label>

            <label class="span-2">Description
                <textarea name="description" rows="4" placeholder="Optional description or notes about this category...">{{ old('description', $category->description) }}</textarea>
            </label>

            <div class="span-2">
                <label class="checkbox-label" for="category-is-active">
                    <input type="checkbox" id="category-is-active" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))>
                    <span>Active category</span>
                </label>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a class="button button-secondary" href="{{ route('categories.index') }}">Cancel</a>
        <button class="button button-primary" type="submit">{{ $category->exists ? 'Save changes' : 'Create category' }} <span>→</span></button>
    </div>
</form>
@endsection
