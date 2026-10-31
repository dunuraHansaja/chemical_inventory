<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('images/inventroo-icon.png') }}">
    <title>Set up administrator · INVENTROO</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page setup-page">
    <section class="auth-card">
        <header class="auth-brand-header" style="text-align: center; margin-bottom: 24px;">
            <a class="brand auth-brand" href="{{ route('setup') }}" style="display: inline-flex; justify-content: center; margin-bottom: 12px;">
                <img class="brand-logo" src="{{ asset('images/inventroo-logo.png') }}" alt="INVENTROO — Inventory Management Platform" style="max-width: 220px; height: auto;">
            </a>
            <span class="eyebrow" style="display: block; margin-bottom: 6px;">FIRST-TIME SETUP</span>
            <h1 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0 0 6px 0;">Create the administrator</h1>
            <p class="muted" style="margin: 0; font-size: 13px; color: #64748b;">This setup page closes automatically after the first account is created.</p>
        </header>

        @include('components.flash-messages')

        <form method="POST" action="{{ route('setup.store') }}" class="form-stack">
            @csrf
            <label>Your full name <span class="required">*</span>
                <input name="name" value="{{ old('name') }}" autocomplete="name" placeholder="e.g. Alex Morgan" required autofocus>
            </label>

            <label>Email address <span class="required">*</span>
                <input type="email" name="email" value="{{ old('email') }}" autocomplete="username" placeholder="e.g. admin@company.com" required>
            </label>

            <label>Password <span class="required">*</span> <small>10 characters minimum</small>
                <input type="password" name="password" autocomplete="new-password" minlength="10" placeholder="••••••••••••" required>
            </label>

            <label>Confirm password <span class="required">*</span>
                <input type="password" name="password_confirmation" autocomplete="new-password" minlength="10" placeholder="••••••••••••" required>
            </label>

            <button class="button button-primary button-wide" type="submit" style="min-height: 44px; margin-top: 8px;">Create administrator <span>→</span></button>
        </form>

        <div class="auth-foot">The first account is your primary system administrator.</div>
    </section>
</body>
</html>
