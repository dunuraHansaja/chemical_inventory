<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('images/inventroo-icon.png') }}">
    <title>Set up administrator · INVENTROO</title>@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <section class="auth-card">
        <a class="brand auth-brand" href="{{ route('setup') }}"><img class="brand-logo" src="{{ asset('images/inventroo-logo.png') }}" alt="INVENTROO — Inventory Management Platform"></a>
        <span class="eyebrow">FIRST-TIME SETUP</span><h1>Create the administrator</h1><p class="muted">This setup page closes automatically after the first account is created.</p>
        @include('components.flash-messages')
        <form method="POST" action="{{ route('setup.store') }}" class="form-stack">@csrf
            <label>Your name<input name="name" value="{{ old('name') }}" autocomplete="name" required autofocus></label>
            <label>Email address<input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required></label>
            <label>Password <small>10 characters minimum</small><input type="password" name="password" autocomplete="new-password" required></label>
            <label>Confirm password<input type="password" name="password_confirmation" autocomplete="new-password" required></label>
            <button class="button button-primary button-wide" type="submit">Create administrator <span>→</span></button>
        </form>
        <div class="auth-foot">The first account is your administrator account.</div>
    </section>
</body>
</html>
