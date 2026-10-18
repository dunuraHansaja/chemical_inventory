<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('images/inventroo-icon.png') }}">
    <title>Sign in · INVENTROO</title>@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <section class="auth-card">
        <a class="brand auth-brand" href="{{ route('login') }}"><img class="brand-logo" src="{{ asset('images/inventroo-logo.png') }}" alt="INVENTROO — Inventory Management Platform"></a>
        <span class="eyebrow">WELCOME BACK</span><h1>Sign in to your workspace</h1><p class="muted">Manage your materials, stock and operations in one place.</p>
        @include('components.flash-messages')
        <form method="POST" action="{{ route('login.store') }}" class="form-stack">@csrf
            <label>Email address<input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus></label>
            <label>Password<input type="password" name="password" autocomplete="current-password" required></label>
            <label class="checkbox-label"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
            <button class="button button-primary button-wide" type="submit">Sign in <span>→</span></button>
        </form>
        <div class="auth-foot">Secure access to your inventory workspace</div>
    </section>
</body>
</html>
