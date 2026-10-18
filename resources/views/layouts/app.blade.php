<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/inventroo-icon.png') }}">
    <title>@yield('title', 'Dashboard') · INVENTROO</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="@yield('page-class')">
    <div class="app-shell">
        @php($navUser = auth()->user())
        <aside class="sidebar">
            <a class="brand" href="{{ route($navUser->landingRoute()) }}">
                <img class="brand-logo" src="{{ asset('images/inventroo-logo.png') }}" alt="INVENTROO — Inventory Management Platform">
            </a>
            <nav class="main-nav" aria-label="Main navigation">
                <span class="nav-label">WORKSPACE</span>
                @if($navUser->hasPermission('dashboard.view'))
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg></span>Dashboard
                </a>
                @endif
                @if($navUser->hasPermission('materials.view'))
                <a class="nav-link {{ request()->routeIs('materials.*') ? 'active' : '' }}" href="{{ route('materials.index') }}">
                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="m12 2 9 5-9 5-9-5 9-5Z"/><path d="m3 7 9 5 9-5v10l-9 5-9-5V7Z"/><path d="M12 12v10"/></svg></span>Materials
                </a>
                @endif
                @if($navUser->hasPermission('lots.view'))
                <a class="nav-link {{ request()->routeIs('lots.*') ? 'active' : '' }}" href="{{ route('lots.index') }}">
                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="15" height="16" rx="2"/><path d="M16 2v4M8 2v4M3 9h15"/><circle cx="17.5" cy="17.5" r="4.5"/><path d="M17.5 15v2.7l1.6 1"/></svg></span>Lots &amp; expiry
                </a>
                @endif
                @if($navUser->hasPermission('production.view'))
                <a class="nav-link {{ request()->routeIs('production.*') ? 'active' : '' }}" href="{{ route('production.index') }}">
                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M3 21V8l5-2v4l5-3v5l8-5v14H3Z"/><path d="M8 21v-5h5v5M6 3h3v3"/></svg></span>Production
                </a>
                @endif
                @if($navUser->hasPermission('purchases.view'))
                <a class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}" href="{{ route('purchases.index') }}">
                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M2 3h2l2.4 12.2a2 2 0 0 0 2 1.6h9.8a2 2 0 0 0 1.9-1.4L22 8H5"/><circle cx="9" cy="21" r="1"/><circle cx="19" cy="21" r="1"/></svg></span>Purchases
                </a>
                @endif
                @if($navUser->hasPermission('suppliers.view'))
                <a class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">
                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><circle cx="9" cy="8" r="4"/><path d="M2 21v-1a7 7 0 0 1 14 0v1H2ZM16 4.5a4 4 0 0 1 0 7.5M18 14a5 5 0 0 1 4 5v2h-4"/></svg></span>Suppliers
                </a>
                @endif
                @if($navUser->hasPermission('categories.view'))
                <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0l-7.2-7.2a2 2 0 0 1 0-2.8l7.2-7.2A2 2 0 0 1 12 2.8h7a2 2 0 0 1 2 2v7a2 2 0 0 1-.4 1.6Z"/><circle cx="16.5" cy="7.5" r="1"/></svg></span>Categories
                </a>
                @endif
                @if($navUser->hasPermission('movements.view') || $navUser->hasPermission('adjustments.create'))
                <span class="nav-label nav-label-spaced">INVENTORY</span>
                @if($navUser->hasPermission('movements.view'))
                <a class="nav-link {{ request()->routeIs('movements.*') ? 'active' : '' }}" href="{{ route('movements.index') }}">
                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M3 7h17l-4-4M21 17H4l4 4"/></svg></span>Stock movements
                </a>
                @endif
                @if($navUser->hasPermission('adjustments.create'))
                <a class="nav-link {{ request()->routeIs('adjustments.*') ? 'active' : '' }}" href="{{ route('adjustments.create') }}">
                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M4 6h16M4 12h16M4 18h16"/><circle cx="8" cy="6" r="2"/><circle cx="16" cy="12" r="2"/><circle cx="10" cy="18" r="2"/></svg></span>Adjust stock
                </a>
                @endif
                @endif
                @if($navUser->hasPermission('settings.view') || $navUser->hasPermission('roles.manage') || $navUser->hasPermission('users.manage'))
                <details class="nav-settings" @if(request()->routeIs('settings.*')) open @endif>
                    <summary class="nav-link nav-settings-toggle {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1 1.4 1.1-1.4 2.4-1.7-.6a8 8 0 0 1-1.5.9l-.3 1.8h-2.8l-.3-1.8a8 8 0 0 1-1.5-.9l-1.7.6-1.4-2.4 1.4-1.1a8 8 0 0 1 0-1.8L6.9 12l1.4-2.4 1.7.6a8 8 0 0 1 1.5-.9l.3-1.8h2.8l.3 1.8a8 8 0 0 1 1.5.9l1.7-.6 1.4 2.4-1.4 1.1a8 8 0 0 1-.7 1.9Z"/></svg></span>
                        <span>Settings</span>
                        <svg class="nav-settings-chevron" viewBox="0 0 16 16" aria-hidden="true"><path d="m3 10 5-5 5 5"/></svg>
                    </summary>
                    <div class="nav-settings-menu">
                        @if($navUser->hasPermission('settings.view'))
                            <a class="nav-settings-link {{ request()->routeIs('settings.edit') ? 'active' : '' }}" href="{{ route('settings.edit') }}"><span></span>General Settings</a>
                        @endif
                        @if($navUser->hasPermission('roles.manage'))
                            <a class="nav-settings-link {{ request()->routeIs('settings.roles.*') ? 'active' : '' }}" href="{{ route('settings.roles.index') }}"><span></span>Roles &amp; Permissions</a>
                        @endif
                        @if($navUser->hasPermission('users.manage'))
                            <a class="nav-settings-link {{ request()->routeIs('settings.users.*') ? 'active' : '' }}" href="{{ route('settings.users.index') }}"><span></span>User Accounts</a>
                        @endif
                    </div>
                </details>
                @endif
            </nav>
            <div class="sidebar-foot">
                <span class="status-dot"></span>
                <span>Inventory system active</span>
                <small>{{ $inventorySettings['currency_code'] }}</small>
            </div>
        </aside>
        <main class="main-area">
            <header class="topbar">
                <button class="mobile-menu-toggle" type="button" data-mobile-menu-toggle aria-label="Open navigation menu" aria-expanded="false">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <a class="mobile-brand" href="{{ route($navUser->landingRoute()) }}">
                    <img src="{{ asset('images/inventroo-logo.png') }}" alt="INVENTROO — Inventory Management Platform">
                </a>
                @if (request()->routeIs('materials.index'))
                    <form class="materials-global-search" method="GET" action="{{ route('materials.index') }}">
                        <span aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg></span>
                        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search materials, CAS no., code…" aria-label="Search materials, CAS number or code">
                    </form>
                @else
                    <div class="breadcrumb">Workspace <span>/</span> @yield('title', 'Dashboard')</div>
                @endif
                <div class="topbar-user">
                    <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="link-button" type="submit">Sign out</button></form>
                </div>
                <details class="mobile-profile">
                    <summary aria-label="Account menu">
                        <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <svg viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="3" r="1"/><circle cx="8" cy="8" r="1"/><circle cx="8" cy="13" r="1"/></svg>
                    </summary>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="link-button" type="submit">Sign out</button></form>
                </details>
            </header>
            <div class="page-wrap">
                <div class="page-heading">
                    <div><span class="eyebrow">@yield('eyebrow', 'CHEMICAL INVENTORY')</span><h1>@yield('title', 'Dashboard')</h1><p>@yield('subtitle')</p></div>
                    <div class="page-actions">@yield('actions')</div>
                </div>
                @include('components.flash-messages')
                @yield('content')
                <footer class="page-footer">INVENTROO <span>·</span> Inventory operations, clearly in control.</footer>
            </div>
        </main>
        <button class="mobile-menu-scrim" type="button" data-mobile-menu-close aria-label="Close navigation menu" tabindex="-1"></button>
        <nav class="mobile-bottom-nav" aria-label="Mobile navigation">
            @if($navUser->hasPermission('dashboard.view'))
                <a class="mobile-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg><span>Dashboard</span></a>
            @endif
            @if($navUser->hasPermission('materials.view'))
                <a class="mobile-nav-link {{ request()->routeIs('materials.*') ? 'active' : '' }}" href="{{ route('materials.index') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 9 5-9 5-9-5 9-5Z"/><path d="m3 7 9 5 9-5v10l-9 5-9-5V7Z"/></svg><span>Materials</span></a>
            @endif
            @if($navUser->hasPermission('lots.view'))
                <a class="mobile-nav-link {{ request()->routeIs('lots.*') ? 'active' : '' }}" href="{{ route('lots.index') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="15" height="16" rx="2"/><path d="M16 2v4M8 2v4M3 9h15"/><circle cx="17.5" cy="17.5" r="4.5"/><path d="M17.5 15v2.7l1.6 1"/></svg><span>Lots &amp; expiry</span></a>
            @endif
            @if($navUser->hasPermission('production.view'))
                <a class="mobile-nav-link {{ request()->routeIs('production.*') ? 'active' : '' }}" href="{{ route('production.index') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21V8l5-2v4l5-3v5l8-5v14H3Z"/><path d="M8 21v-5h5v5M6 3h3v3"/></svg><span>Production</span></a>
            @endif
            @if($navUser->hasPermission('purchases.view'))
                <a class="mobile-nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}" href="{{ route('purchases.index') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 3h2l2.4 12.2a2 2 0 0 0 2 1.6h9.8a2 2 0 0 0 1.9-1.4L22 8H5"/><circle cx="9" cy="21" r="1"/><circle cx="19" cy="21" r="1"/></svg><span>Purchases</span></a>
            @endif
            @if($navUser->hasPermission('suppliers.view'))
                <a class="mobile-nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="4"/><path d="M2 21v-1a7 7 0 0 1 14 0v1H2ZM16 4.5a4 4 0 0 1 0 7.5M18 14a5 5 0 0 1 4 5v2h-4"/></svg><span>Suppliers</span></a>
            @endif
            @if($navUser->hasPermission('categories.view'))
                <a class="mobile-nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0l-7.2-7.2a2 2 0 0 1 0-2.8l7.2-7.2A2 2 0 0 1 12 2.8h7a2 2 0 0 1 2 2v7a2 2 0 0 1-.4 1.6Z"/><circle cx="16.5" cy="7.5" r="1"/></svg><span>Categories</span></a>
            @endif
            @if($navUser->hasPermission('movements.view'))
                <a class="mobile-nav-link {{ request()->routeIs('movements.*') ? 'active' : '' }}" href="{{ route('movements.index') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7h17l-4-4M21 17H4l4 4"/></svg><span>Stock movements</span></a>
            @endif
            @if($navUser->hasPermission('adjustments.create'))
                <a class="mobile-nav-link {{ request()->routeIs('adjustments.*') ? 'active' : '' }}" href="{{ route('adjustments.create') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/><circle cx="8" cy="6" r="2"/><circle cx="16" cy="12" r="2"/><circle cx="10" cy="18" r="2"/></svg><span>Adjust stock</span></a>
            @endif
            @if($navUser->hasPermission('settings.view') || $navUser->hasPermission('roles.manage') || $navUser->hasPermission('users.manage'))
                @php($settingsRoute = $navUser->hasPermission('settings.view') ? 'settings.edit' : ($navUser->hasPermission('roles.manage') ? 'settings.roles.index' : 'settings.users.index'))
                <a class="mobile-nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route($settingsRoute) }}"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1 1.4 1.1-1.4 2.4-1.7-.6a8 8 0 0 1-1.5.9l-.3 1.8h-2.8l-.3-1.8a8 8 0 0 1-1.5-.9l-1.7.6-1.4-2.4 1.4-1.1a8 8 0 0 1 0-1.8L6.9 12l1.4-2.4 1.7.6a8 8 0 0 1 1.5-.9l.3-1.8h2.8l.3 1.8a8 8 0 0 1 1.5.9l1.7-.6 1.4 2.4-1.4 1.1a8 8 0 0 1-.7 1.9Z"/></svg><span>Settings</span></a>
            @endif
        </nav>
    </div>
    @include('components.confirmation-dialog')
</body>
</html>
