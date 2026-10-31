@extends('layouts.app')
@section('title', $user->exists ? 'Edit user account' : 'Create user account')
@section('subtitle', 'Assign one role to control this user’s system permissions.')
@section('actions')<a class="button button-secondary" href="{{ route('settings.users.index') }}">← Back to users</a>@endsection
@section('content')
    <form method="POST" action="{{ $user->exists ? route('settings.users.update', $user) : route('settings.users.store') }}" class="user-account-form" enctype="multipart/form-data" @if($user->exists) data-confirm="Save changes to user account “{{ $user->name }}”?" data-confirm-title="Save account changes?" data-confirm-action="Save Changes" data-confirm-tone="primary" @endif>
        @csrf @if($user->exists)@method('PUT')@endif

        <div class="user-form-header">
            <span class="eyebrow">ACCOUNT DETAILS</span>
            <h2>User information</h2>
            <p class="user-form-subtitle">{{ $user->exists ? 'Update your sign-in details and personal information.' : 'Provide the new user account and personal details.' }}</p>
        </div>

        <div class="user-avatar-card">
            <div class="user-avatar-visual">
                <span class="user-avatar-circle">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }} avatar" data-user-avatar-preview onerror="this.hidden=true; this.parentElement.querySelector('[data-user-avatar-initial]')?.removeAttribute('hidden');">
                        <span data-user-avatar-initial hidden>{{ strtoupper(substr($user->name ?: 'U', 0, 1)) }}</span>
                    @else
                        <span data-user-avatar-initial>{{ strtoupper(substr($user->name ?: 'U', 0, 1)) }}</span>
                        <img alt="" data-user-avatar-preview hidden>
                    @endif
                </span>
            </div>
            <div class="user-avatar-details">
                <strong>Profile avatar</strong>
                <p>Upload a profile picture for this user account. Accepted formats: JPG, PNG (up to 2MB).</p>
                <div class="user-avatar-actions-row">
                    <label class="user-avatar-upload-pill" for="user-avatar-input">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        <span data-user-avatar-label>{{ $user->avatar_path ? 'Change avatar' : 'Upload avatar' }}</span>
                        <input id="user-avatar-input" name="avatar" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png" data-user-avatar-input hidden>
                    </label>
                    @if($user->avatar_path)
                        <label class="user-avatar-remove-pill">
                            <input type="checkbox" name="remove_avatar" value="1" data-user-avatar-remove>
                            <span>Remove current avatar</span>
                        </label>
                    @endif
                </div>
                @error('avatar')<small class="field-error" style="display:block;margin-top:6px;color:#ef4444;font-size:12px;">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="user-form-grid">
            <label class="user-form-field">
                <span class="user-field-label">Name <span class="required">*</span></span>
                <span class="user-input-box" @error('name') style="border-color: #ef4444;" @enderror>
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
                    <input name="name" value="{{ old('name', $user->name) }}" placeholder="Full name" required>
                </span>
                @error('name')<small class="field-error" style="color:#ef4444;font-size:12px;">{{ $message }}</small>@enderror
            </label>

            <label class="user-form-field">
                <span class="user-field-label">Email <span class="required">*</span></span>
                <span class="user-input-box" @error('email') style="border-color: #ef4444;" @enderror>
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="Email address" required>
                </span>
                @error('email')<small class="field-error" style="color:#ef4444;font-size:12px;">{{ $message }}</small>@enderror
            </label>

            <label class="user-form-field">
                <span class="user-field-label">Role <span class="required">*</span></span>
                <span class="user-input-box user-select-box" @error('role_id') style="border-color: #ef4444;" @enderror>
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <select name="role_id" required>
                        <option value="">Select a role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <svg class="select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                </span>
                @error('role_id')<small class="field-error" style="color:#ef4444;font-size:12px;">{{ $message }}</small>@enderror
            </label>

            <div class="user-form-field user-form-checkbox-col">
                <label class="user-active-checkbox-label">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))>
                    <span class="active-account-text">Active account</span>
                </label>
                @error('is_active')<small class="field-error" style="color:#ef4444;font-size:12px;">{{ $message }}</small>@enderror
            </div>
        </div>

        <hr class="user-form-divider">

        <div class="user-form-header">
            <span class="eyebrow">SIGN-IN SECURITY</span>
            <h2>{{ $user->exists ? 'Change password' : 'Initial password' }}</h2>
            <p class="user-form-subtitle">{{ $user->exists ? 'Leave password blank if you don’t want to change it.' : 'Use at least 10 characters for the initial password.' }}</p>
        </div>

        <div class="user-form-grid">
            <label class="user-form-field">
                <span class="user-field-label">Password @if(!$user->exists)<span class="required">*</span>@endif</span>
                <span class="user-input-box" @error('password') style="border-color: #ef4444;" @enderror>
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3"/></svg>
                    <input type="password" name="password" minlength="10" @required(!$user->exists) autocomplete="new-password" placeholder="Enter new password">
                </span>
                @error('password')<small class="field-error" style="color:#ef4444;font-size:12px;">{{ $message }}</small>@enderror
            </label>

            <label class="user-form-field">
                <span class="user-field-label">Confirm password @if(!$user->exists)<span class="required">*</span>@endif</span>
                <span class="user-input-box">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3"/></svg>
                    <input type="password" name="password_confirmation" minlength="10" @required(!$user->exists) autocomplete="new-password" placeholder="Confirm new password">
                </span>
            </label>
        </div>

        <div class="user-form-actions-bar">
            <a class="button user-btn-cancel" href="{{ route('settings.users.index') }}">Cancel</a>
            <button class="button user-btn-submit" type="submit">{{ $user->exists ? 'Save account' : 'Create account' }} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg></button>
        </div>
    </form>
@endsection
