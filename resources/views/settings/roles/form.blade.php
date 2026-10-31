@extends('layouts.app')
@section('title', $role->exists ? 'Edit role' : 'Create role')
@section('subtitle', 'Choose a role name and the actions assigned users may perform.')
@section('actions')
    <a class="button button-secondary" href="{{ route('settings.roles.index') }}">← Back to roles</a>
@endsection

@section('content')
    @if($role->is_system)
        <section class="panel settings-note form-narrow" style="margin: 0 auto;">
            <span class="eyebrow">SYSTEM ROLE</span>
            <h2>Administrator</h2>
            <p>This protected system role has full access and cannot be changed or deleted.</p>
        </section>
    @else
        <form method="POST" action="{{ $role->exists ? route('settings.roles.update', $role) : route('settings.roles.store') }}" class="panel form-panel" @if($role->exists) data-confirm="Save changes to role “{{ $role->name }}”?" data-confirm-title="Save role changes?" data-confirm-action="Save Changes" data-confirm-tone="primary" @endif>
            @csrf
            @if($role->exists)
                @method('PUT')
            @endif

            <div class="form-section">
                <span class="eyebrow">ROLE DETAILS</span>
                <h2>Role identity</h2>
                <p class="form-hint">Assign a descriptive title that clarifies this role's purpose within the team.</p>

                <div class="form-grid">
                    <label class="span-2">Role name <span class="required">*</span>
                        <input name="name" value="{{ old('name', $role->name) }}" maxlength="100" placeholder="e.g. Inventory Manager, Lab Technician, Purchasing Officer" required>
                    </label>
                </div>
            </div>

            <div class="form-section">
                <span class="eyebrow">ACCESS CONTROL</span>
                <h2>Permissions matrix</h2>
                <p class="form-hint">Select the exact modules and actions users assigned to this role may perform.</p>

                <div class="permission-groups">
                    @foreach($permissionGroups as $group => $permissions)
                        <fieldset class="permission-group">
                            <legend>{{ $group }}</legend>
                            <div class="permission-options">
                                @foreach($permissions as $key => $label)
                                    <label class="permission-option">
                                        <input type="checkbox" name="permissions[]" value="{{ $key }}" @checked(in_array($key, old('permissions', $role->permissions ?? []), true))>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                </div>
            </div>

            <div class="form-actions">
                <a class="button button-secondary" href="{{ route('settings.roles.index') }}">Cancel</a>
                <button class="button button-primary" type="submit">{{ $role->exists ? 'Save changes' : 'Create role' }} <span>→</span></button>
            </div>
        </form>
    @endif
@endsection
