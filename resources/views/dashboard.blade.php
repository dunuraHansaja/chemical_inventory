@extends('layouts.app')
@section('title', 'Dashboard')
@section('subtitle', 'A clear view of your materials, stock health and daily operations.')
@section('actions')
    <a class="button button-secondary" href="{{ route('materials.create') }}">＋ Add material</a>
    <a class="button button-primary" href="{{ route('purchases.create') }}">＋ New purchase</a>
@endsection
@section('content')
    <div class="welcome-banner">
        <div><span class="banner-kicker">INVENTORY OVERVIEW · {{ now()->format('l, d M Y') }}</span><h2>Your inventory, at a glance.</h2><p>Track stock levels, stay ahead of expiry and keep every movement accounted for.</p></div>
        <div class="banner-art" aria-hidden="true"><span>◉</span><i></i><b></b></div>
    </div>
    <div class="stats-grid">
        <article class="stat-card"><span class="stat-icon teal">▤</span><span class="stat-label">Active materials</span><strong>{{ number_format($materialCount) }}</strong><small>Across your catalogue</small></article>
        <article class="stat-card"><span class="stat-icon blue">♧</span><span class="stat-label">Active suppliers</span><strong>{{ number_format($supplierCount) }}</strong><small>Available for purchasing</small></article>
        <article class="stat-card"><span class="stat-icon amber">!</span><span class="stat-label">Low stock items</span><strong>{{ number_format($lowStockCount) }}</strong><small>At or below reorder level</small></article>
        <article class="stat-card"><span class="stat-icon purple">↙</span><span class="stat-label">Draft purchases</span><strong>{{ number_format($openPurchases) }}</strong><small>Awaiting receipt into stock</small></article>
    </div>
    <div class="dashboard-grid">
        <section class="panel">
            <div class="panel-heading"><div><h2>Expiry watch</h2><p>Lots expiring in the next 90 days</p></div><a class="text-link" href="{{ route('lots.index', ['filter' => 'expiring']) }}">View all <span>→</span></a></div>
            @forelse ($expiryLots as $lot)
                <a class="list-row" href="{{ route('materials.show', $lot->material) }}"><span class="list-icon amber-bg">⌁</span><span class="list-main"><strong>{{ $lot->material->name }}</strong><small>Lot {{ $lot->lot_number }} · {{ number_format((float) $lot->quantity_on_hand, 3) }} {{ $lot->material->stock_unit }}</small></span><span class="expiry-date">{{ $lot->expiry_date->format('d M Y') }}<small>{{ $lot->expiry_date->diffForHumans() }}</small></span></a>
            @empty
                <div class="empty-state"><span class="empty-mark">✓</span><strong>Nothing expiring soon</strong><p>Lots inside the 90-day window will show here.</p></div>
            @endforelse
        </section>
        <section class="panel value-panel">
            <span class="eyebrow">CURRENT STOCK VALUE</span><strong class="value-total">{{ $inventorySettings['currency_symbol'] }}{{ number_format((float) $stockValue, 2) }}</strong><p>Based on quantity on hand × recorded unit cost.</p>
            <div class="value-divider"></div><a class="button button-secondary button-wide" href="{{ route('materials.index') }}">Browse materials <span>→</span></a>
        </section>
    </div>
    <section class="panel movement-panel">
        <div class="panel-heading"><div><h2>Recent stock movements</h2><p>Latest changes across your inventory</p></div><a class="text-link" href="{{ route('movements.index') }}">Movement history <span>→</span></a></div>
        <div class="table-wrap"><table><thead><tr><th>MATERIAL</th><th>MOVEMENT</th><th>QUANTITY</th><th>BALANCE AFTER</th><th>RECORDED</th></tr></thead><tbody>
        @forelse ($recentMovements as $movement)
            <tr><td><a class="table-primary" href="{{ route('materials.show', $movement->material) }}">{{ $movement->material->name }}</a><small class="table-secondary">{{ $movement->material->item_code }}</small></td><td><span class="badge badge-neutral">{{ str_replace('_', ' ', $movement->movement_type) }}</span></td><td class="{{ (float) $movement->quantity < 0 ? 'text-danger' : 'text-success' }}">{{ (float) $movement->quantity > 0 ? '+' : '' }}{{ number_format((float) $movement->quantity, 3) }} {{ $movement->material->stock_unit }}</td><td>{{ $movement->balance_after === null ? '—' : number_format((float) $movement->balance_after, 3) }}</td><td>{{ $movement->created_at->format('d M, H:i') }}</td></tr>
        @empty<tr><td colspan="5"><div class="table-empty">No movements have been recorded yet.</div></td></tr>@endforelse
        </tbody></table></div>
    </section>
@endsection
