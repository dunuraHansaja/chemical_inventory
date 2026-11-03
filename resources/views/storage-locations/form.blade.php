@extends('layouts.app')
@section('title', $storageLocation->exists ? 'Edit storage location' : 'Add storage location')
@section('subtitle', 'Maintain physical storage locations and facilities.')
@section('actions')
    <a class="button button-secondary" href="{{ route('storage-locations.index') }}">← Back to storage locations</a>
@endsection

@section('content')
<form method="POST"
      action="{{ $storageLocation->exists ? route('storage-locations.update', $storageLocation) : route('storage-locations.store') }}"
      class="panel form-panel form-narrow"
      @if($storageLocation->exists)
          data-confirm="Save changes to the storage location “{{ $storageLocation->name }}”?"
          data-confirm-title="Save storage location changes?"
          data-confirm-action="Save Changes"
          data-confirm-tone="primary"
      @endif>
    @csrf
    @if($storageLocation->exists)
        @method('PUT')
    @endif

    <div class="form-section">
        <span class="eyebrow">STORAGE LOCATION DETAILS</span>
        <h2>Storage location information</h2>
        <p class="form-hint">Storage location details help organize where materials and products are kept.</p>

        <div class="form-grid">
            <label class="span-2">Storage Location Name <span class="required">*</span>
                <input name="name"
                       value="{{ old('name', $storageLocation->name) }}"
                       placeholder="e.g. Main Warehouse, Cold Room, Shelf A-2"
                       required>
            </label>

            <label class="span-2">Storage Location Address
                <textarea name="address"
                          rows="4"
                          placeholder="Optional physical address or location notes...">{{ old('address', $storageLocation->address) }}</textarea>
            </label>

            <div class="span-2">
                <label class="checkbox-label" for="location-is-active">
                    <input type="checkbox"
                           id="location-is-active"
                           name="is_active"
                           value="1"
                           @checked(old('is_active', $storageLocation->is_active ?? true))>
                    <span class="checkbox-card-copy">
                        <strong>Active storage location</strong>
                        <small>Make this location available in storage location dropdowns when receiving or editing stock.</small>
                    </span>
                </label>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a class="button button-secondary" href="{{ route('storage-locations.index') }}">Cancel</a>
        <button class="button button-primary" type="submit">
            {{ $storageLocation->exists ? 'Save changes' : 'Create storage location' }} <span>→</span>
        </button>
    </div>
</form>
@endsection

