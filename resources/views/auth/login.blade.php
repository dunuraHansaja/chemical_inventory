<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('images/inventroo-icon.png') }}">
    <title>Sign in · INVENTROO</title>@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page login-page">
    <main class="login-card" aria-labelledby="login-title">
        <header class="login-header">
            <a class="login-mark" href="{{ route('login') }}" aria-label="INVENTROO sign in">
                <img src="{{ asset('images/inventroo-icon.png') }}" alt="">
            </a>
            <p class="login-brand">INVENTROO <span>INVENTORY MANAGEMENT</span></p>
            <h1 id="login-title">Welcome Back</h1>
            <p class="login-subtitle">Sign in to continue</p>
        </header>
        @include('components.flash-messages')
        <form method="POST" action="{{ route('login.store') }}" class="login-form">@csrf
            <label class="login-field">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2H4Z"/></svg>
                <input type="email" name="email" value="{{ old('email') }}" autocomplete="username" placeholder="Username or Email" aria-label="Email address" required autofocus>
            </label>
            <label class="login-field">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="12" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v4"/></svg>
                <input id="login-password" type="password" name="password" autocomplete="current-password" placeholder="Password" aria-label="Password" required>
                <button class="login-password-toggle" type="button" aria-label="Show password" aria-controls="login-password" aria-pressed="false" data-password-toggle>
                    <svg viewBox="0 0 24 24" aria-hidden="true" data-password-hidden><path d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c5 0 8.5 4.4 9.5 7-.4 1-1.2 2.3-2.4 3.5M6.2 6.2C3.9 7.7 2.7 9.8 2.5 12c.5 1.3 1.5 2.8 3.1 4.1A10.1 10.1 0 0 0 12 19c.8 0 1.5-.1 2.2-.3"/></svg>
                    <svg viewBox="0 0 24 24" aria-hidden="true" data-password-visible hidden><path d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </label>
            <div class="login-options">
                <label class="login-remember">
                    <input type="checkbox" name="remember" value="1">
                    <span class="login-checkbox" aria-hidden="true"><svg viewBox="0 0 16 16"><path d="m3 8 3.2 3.2L13 4.5"/></svg></span>
                    <span>Remember Me</span>
                </label>
                <span class="login-secure"><svg viewBox="0 0 20 20" aria-hidden="true"><rect x="4" y="8" width="12" height="9" rx="2"/><path d="M6.5 8V6a3.5 3.5 0 0 1 7 0v2"/></svg>Secure sign in</span>
            </div>
            <button class="login-submit" type="submit">
                <span>Login</span>
                <span class="login-submit-arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 12h15m-6-6 6 6-6 6"/></svg></span>
            </button>
        </form>
        <p class="login-footer">MANAGE YOUR MATERIALS <span>•</span> TRACK YOUR STOCK <span>•</span> GROW TOGETHER</p>
    </main>
</body>
</html>
