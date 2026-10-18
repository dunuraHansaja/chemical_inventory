@extends('layouts.app')
@section('title', $user->exists ? 'Edit user account' : 'Create user account')
@section('subtitle', 'Assign one role to control this user’s system permissions.')
@section('actions')<a class="button button-secondary" href="{{ route('settings.users.index') }}">← Back to users</a>@endsection
@section('content')
    <form method="POST" action="{{ $user->exists ? route('settings.users.update', $user) : route('settings.users.store') }}" class="panel form-panel" @if($user->exists) data-confirm="Save changes to user account “{{ $user->name }}”?" data-confirm-title="Save account changes?" data-confirm-action="Save Changes" data-confirm-tone="primary" @endif>
        @csrf @if($user->exists)@method('PUT')@endif
        <div class="form-section"><span class="eyebrow">ACCOUNT DETAILS</span><h2>User information</h2><div class="form-grid">
            <label>Name <span class="required">*</span><input name="name" value="{{ old('name', $user->name) }}" required></label>
            <label>Email <span class="required">*</span><input type="email" name="email" value="{{ old('email', $user->email) }}" required></label>
            <label>Role <span class="required">*</span><select name="role_id" required><option value="">Select a role</option>@foreach($roles as $role)<option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>{{ $role->name }}</option>@endforeach</select></label>
            <label class="checkbox-label"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))> Active account</label>
        </div></div>
        <div class="form-section"><span class="eyebrow">SIGN-IN SECURITY</span><h2>{{ $user->exists ? 'Change password' : 'Initial password' }}</h2><div class="form-grid">
            <label class="span-2">Password @if(!$user->exists)<span class="required">*</span>@endif<input type="password" name="password" minlength="10" @required(!$user->exists) autocomplete="new-password">@if($user->exists)<small>Leave blank to keep the current password.</small>@else<small>Use at least 10 characters.</small>@endif</label>
            <label class="span-2">Confirm password<input type="password" name="password_confirmation" minlength="10" @required(!$user->exists) autocomplete="new-password"></label>
        </div></div>
        <div class="form-actions"><a class="button button-secondary" href="{{ route('settings.users.index') }}">Cancel</a><button class="button button-primary" type="submit">{{ $user->exists ? 'Save account' : 'Create account' }} <span>→</span></button></div>
    </form>
@endsection
