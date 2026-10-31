@extends('layouts.app')
@section('title', $supplier->exists ? 'Edit supplier' : 'Add supplier')
@section('subtitle', 'Keep supplier contacts and tax details close to your purchase records.')
@section('actions')
    <a class="button button-secondary" href="{{ route('suppliers.index') }}">← Back to suppliers</a>
@endsection

@section('content')
<form method="POST" action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}" class="panel form-panel" @if($supplier->exists) data-confirm="Save changes to supplier “{{ $supplier->name }}”?" data-confirm-title="Save supplier changes?" data-confirm-action="Save Changes" data-confirm-tone="primary" @endif>
    @csrf
    @if($supplier->exists)
        @method('PUT')
    @endif

    <div class="form-section">
        <span class="eyebrow">SUPPLIER PROFILE</span>
        <h2>Business details</h2>
        <p class="form-hint">Keep supplier contacts, identifiers, and tax details close to your purchase records.</p>

        <div class="form-grid">
            <label>Supplier code <span class="required">*</span>
                <input name="supplier_code" value="{{ old('supplier_code', $supplier->supplier_code) }}" maxlength="50" placeholder="e.g. SUP-001" required>
            </label>

            <label>Supplier name <span class="required">*</span>
                <input name="name" value="{{ old('name', $supplier->name) }}" placeholder="e.g. Apex Chemical Supplies Ltd" required>
            </label>

            <label>Contact person
                <input name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}" placeholder="e.g. Jane Doe">
            </label>

            <label>Tax number
                <input name="tax_number" value="{{ old('tax_number', $supplier->tax_number) }}" placeholder="e.g. VAT-98765432">
            </label>

            <label>Email address
                <input type="email" name="email" value="{{ old('email', $supplier->email) }}" placeholder="e.g. orders@supplier.com">
            </label>

            <label>Phone number
                <input name="phone" value="{{ old('phone', $supplier->phone) }}" placeholder="e.g. +1 (555) 019-2834">
            </label>

            <label class="span-2">Address
                <textarea name="address" rows="3" placeholder="Street address, city, state, postal code...">{{ old('address', $supplier->address) }}</textarea>
            </label>

            <div class="span-2">
                <label class="checkbox-label" for="supplier-is-active">
                    <input type="checkbox" id="supplier-is-active" name="is_active" value="1" @checked(old('is_active', $supplier->is_active ?? true))>
                    <span>Active supplier</span>
                </label>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a class="button button-secondary" href="{{ route('suppliers.index') }}">Cancel</a>
        <button class="button button-primary" type="submit">{{ $supplier->exists ? 'Save changes' : 'Create supplier' }} <span>→</span></button>
    </div>
</form>
@endsection
