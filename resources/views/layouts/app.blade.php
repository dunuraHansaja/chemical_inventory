<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · ChemStock</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="@yield('page-class')">
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="{{ route('dashboard') }}">
                <span class="brand-mark">C</span>
                <span><strong>ChemStock</strong><small>INVENTORY SYSTEM</small></span>
            </a>
            <nav class="main-nav" aria-label="Main navigation">
                <span class="nav-label">WORKSPACE</span>
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="nav-icon">◫</span>Dashboard</a>
                <a class="nav-link {{ request()->routeIs('materials.*') ? 'active' : '' }}" href="{{ route('materials.index') }}"><span class="nav-icon">▤</span>Materials</a>
                <a class="nav-link {{ request()->routeIs('lots.*') ? 'active' : '' }}" href="{{ route('lots.index') }}"><span class="nav-icon">▦</span>Lots &amp; expiry</a>
                <a class="nav-link {{ request()->routeIs('production.*') ? 'active' : '' }}" href="{{ route('production.index') }}"><span class="nav-icon">⚙</span>Production</a>
                <a class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}" href="{{ route('purchases.index') }}"><span class="nav-icon">↙</span>Purchases</a>
                <a class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}"><span class="nav-icon">♧</span>Suppliers</a>
                <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}"><span class="nav-icon">◈</span>Categories</a>
                <span class="nav-label nav-label-spaced">INVENTORY</span>
                <a class="nav-link {{ request()->routeIs('movements.*') ? 'active' : '' }}" href="{{ route('movements.index') }}"><span class="nav-icon">⇄</span>Stock movements</a>
                <a class="nav-link {{ request()->routeIs('adjustments.*') ? 'active' : '' }}" href="{{ route('adjustments.create') }}"><span class="nav-icon">±</span>Adjust stock</a>
                <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.edit') }}"><span class="nav-icon">⚙</span>Settings <span class="nav-vat">VAT</span></a>
            </nav>
            <div class="sidebar-foot">
                <span class="status-dot"></span>
                <span>Inventory system active</span>
                <small>{{ $inventorySettings['currency_code'] }}</small>
            </div>
        </aside>
        <main class="main-area">
            <header class="topbar">
                @if (request()->routeIs('materials.index'))
                    <form class="materials-global-search" method="GET" action="{{ route('materials.index') }}">
                        <span aria-hidden="true">⌕</span>
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
            </header>
            <div class="page-wrap">
                <div class="page-heading">
                    <div><span class="eyebrow">@yield('eyebrow', 'CHEMICAL INVENTORY')</span><h1>@yield('title', 'Dashboard')</h1><p>@yield('subtitle')</p></div>
                    <div class="page-actions">@yield('actions')</div>
                </div>
                @include('components.flash-messages')
                @yield('content')
                <footer class="page-footer">ChemStock <span>·</span> Inventory operations, clearly in control.</footer>
            </div>
        </main>
    </div>
    @include('components.confirmation-dialog')
</body>
</html>
