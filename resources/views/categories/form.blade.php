@extends('layouts.app')
@section('title', $category->exists ? 'Edit category' : 'Add category')
@section('subtitle', 'Category names help keep the materials catalogue organized.')
@section('content')
<form method="POST" action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}" class="panel form-panel form-narrow">@csrf @if($category->exists)@method('PUT')@endif
<div class="form-section"><div class="form-grid"><label>Category name <span class="required">*</span><input name="name" value="{{ old('name', $category->name) }}" required></label><label class="span-2">Description<textarea name="description" rows="4">{{ old('description', $category->description) }}</textarea></label><label class="checkbox-label"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))> Active category</label></div></div>
<div class="form-actions"><a class="button button-secondary" href="{{ route('categories.index') }}">Cancel</a><button class="button button-primary" type="submit">{{ $category->exists ? 'Save changes' : 'Create category' }}</button></div></form>
@endsection
