@extends('layouts.app')
@section('title', $supplier->exists ? 'Edit supplier' : 'Add supplier')
@section('subtitle', 'Keep supplier contacts and tax details close to your purchase records.')
@section('actions')<a class="button button-secondary" href="{{ route('suppliers.index') }}">← Back to suppliers</a>@endsection
@section('content')
<form method="POST" action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}" class="panel form-panel">@csrf @if($supplier->exists)@method('PUT')@endif
<div class="form-section"><span class="eyebrow">SUPPLIER PROFILE</span><h2>Business details</h2><div class="form-grid">
<label>Supplier code <span class="required">*</span><input name="supplier_code" value="{{ old('supplier_code', $supplier->supplier_code) }}" maxlength="50" required></label><label>Supplier name <span class="required">*</span><input name="name" value="{{ old('name', $supplier->name) }}" required></label><label>Contact person<input name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}"></label><label>Tax number<input name="tax_number" value="{{ old('tax_number', $supplier->tax_number) }}"></label><label>Email address<input type="email" name="email" value="{{ old('email', $supplier->email) }}"></label><label>Phone number<input name="phone" value="{{ old('phone', $supplier->phone) }}"></label><label class="span-2">Address<textarea name="address" rows="3">{{ old('address', $supplier->address) }}</textarea></label><label class="checkbox-label"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $supplier->is_active ?? true))> Active supplier</label>
</div></div><div class="form-actions"><a class="button button-secondary" href="{{ route('suppliers.index') }}">Cancel</a><button class="button button-primary" type="submit">{{ $supplier->exists ? 'Save changes' : 'Create supplier' }} <span>→</span></button></div></form>
@endsection
