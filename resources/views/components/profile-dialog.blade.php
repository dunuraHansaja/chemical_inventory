<dialog class="profile-dialog" data-profile-dialog @if($errors->profile->any()) data-profile-invalid @endif aria-labelledby="profile-dialog-title">
    <form method="POST" action="{{ route('profile.update') }}" class="profile-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <header class="profile-dialog-header">
            <div>
                <span class="eyebrow">YOUR ACCOUNT</span>
                <h2 id="profile-dialog-title">Profile details</h2>
                <p>Update your sign-in details and personal information.</p>
            </div>
            <button class="profile-dialog-close" type="button" data-profile-close aria-label="Close profile">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
            </button>
        </header>
        @if($errors->profile->any())
            <div class="profile-errors" role="alert">
                <strong>We couldn't save your profile.</strong>
                <ul>@foreach($errors->profile->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        <div class="profile-identity">
            <span class="profile-avatar">
                @if($profileUser->avatar_url)
                    <img src="{{ $profileUser->avatar_url }}" alt="{{ $profileUser->name }} profile avatar" data-profile-avatar-preview onerror="this.hidden=true; this.parentElement.querySelector('[data-profile-avatar-initial]')?.removeAttribute('hidden');">
                    <span data-profile-avatar-initial hidden>{{ strtoupper(substr($profileUser->name, 0, 1)) }}</span>
                @else
                    <span data-profile-avatar-initial>{{ strtoupper(substr($profileUser->name, 0, 1)) }}</span>
                    <img alt="" data-profile-avatar-preview hidden>
                @endif
            </span>
            <span class="profile-identity-copy">
                <strong>{{ $profileUser->name }}</strong>
                <span class="profile-role">{{ $profileUser->role?->name ?? 'User' }}</span>
                <small><i></i>{{ $profileUser->is_active ? 'Active account' : 'Inactive account' }}</small>
            </span>
            <label class="profile-avatar-upload" for="profile-avatar">
                <span class="profile-avatar-upload-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h3l1.5-2h7L17 7h3v12H4z"/><circle cx="12" cy="13" r="3.5"/></svg></span>
                <span><strong>Change avatar</strong><small>JPG, PNG (Max 2MB)</small></span>
                <input id="profile-avatar" name="avatar" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png" data-profile-avatar-input>
            </label>
            @error('avatar', 'profile')<small class="profile-field-error profile-avatar-error">{{ $message }}</small>@enderror
        </div>
        <section class="profile-section" aria-labelledby="profile-personal-title">
            <h3 id="profile-personal-title"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20v-1a7 7 0 0 1 14 0v1z"/></svg>Personal information</h3>
            <div class="profile-fields">
                <label>Name
                    <span class="profile-input-wrap"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20v-1a7 7 0 0 1 14 0v1z"/></svg><input name="name" value="{{ old('name', $profileUser->name) }}" autocomplete="name" maxlength="255" required></span>
                    @error('name', 'profile')<small class="profile-field-error">{{ $message }}</small>@enderror
                </label>
                <label>Email address
                    <span class="profile-input-wrap"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg><input type="email" name="email" value="{{ old('email', $profileUser->email) }}" autocomplete="email" maxlength="191" required></span>
                    @error('email', 'profile')<small class="profile-field-error">{{ $message }}</small>@enderror
                </label>
            </div>
        </section>
        <div class="profile-password-section">
            <h3><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3M12 14v3"/></svg>Sign-in security</h3>
            <p>Leave password blank if you don't want to change it.</p>
            <div class="profile-fields">
                <label>New password
                    <span class="profile-input-wrap"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3"/></svg><input id="profile-password" type="password" name="password" autocomplete="new-password" minlength="10" placeholder="Enter new password"><button class="profile-password-toggle" type="button" aria-label="Show new password" aria-controls="profile-password" aria-pressed="false" data-password-toggle><svg viewBox="0 0 24 24" aria-hidden="true" data-password-hidden><path d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c5 0 8.5 4.4 9.5 7-.4 1-1.2 2.3-2.4 3.5M6.2 6.2C3.9 7.7 2.7 9.8 2.5 12c.5 1.3 1.5 2.8 3.1 4.1A10.1 10.1 0 0 0 12 19c.8 0 1.5-.1 2.2-.3"/></svg><svg viewBox="0 0 24 24" aria-hidden="true" data-password-visible hidden><path d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z"/><circle cx="12" cy="12" r="3"/></svg></button></span>
                    @error('password', 'profile')<small class="profile-field-error">{{ $message }}</small>@enderror
                </label>
                <label>Confirm new password
                    <span class="profile-input-wrap"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3"/></svg><input id="profile-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="10" placeholder="Confirm new password"><button class="profile-password-toggle" type="button" aria-label="Show password confirmation" aria-controls="profile-password-confirmation" aria-pressed="false" data-password-toggle><svg viewBox="0 0 24 24" aria-hidden="true" data-password-hidden><path d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c5 0 8.5 4.4 9.5 7-.4 1-1.2 2.3-2.4 3.5M6.2 6.2C3.9 7.7 2.7 9.8 2.5 12c.5 1.3 1.5 2.8 3.1 4.1A10.1 10.1 0 0 0 12 19c.8 0 1.5-.1 2.2-.3"/></svg><svg viewBox="0 0 24 24" aria-hidden="true" data-password-visible hidden><path d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z"/><circle cx="12" cy="12" r="3"/></svg></button></span>
                </label>
            </div>
            <p class="profile-password-hint"><svg viewBox="0 0 20 20" aria-hidden="true"><circle cx="10" cy="10" r="8"/><path d="M10 9v5M10 6.3h.01"/></svg>Password must be at least 10 characters.</p>
        </div>
        <footer class="profile-dialog-actions">
            <button class="button button-secondary" type="button" data-profile-close>Cancel</button>
            <button class="button button-primary" type="submit">Save profile <span>→</span></button>
        </footer>
    </form>
</dialog>
