@extends('layouts.app')
@section('title', 'Dashboard')
@section('subtitle', 'A clear view of your materials, stock health and daily operations.')
@section('actions')
    @if(auth()->user()->hasPermission('materials.create'))<a class="button button-secondary" href="{{ route('materials.create') }}">＋ Add material</a>@endif
    @if(auth()->user()->hasPermission('purchases.create'))<a class="button button-primary" href="{{ route('purchases.create') }}">＋ New purchase</a>@endif
@endsection
@section('content')
    <div class="welcome-banner">
        <div><span class="banner-kicker">INVENTORY OVERVIEW · {{ now()->format('l, d M Y') }}</span><h2>Your inventory, at a glance.</h2><p>Track stock levels, stay ahead of expiry and keep every movement accounted for.</p></div>
        <div class="banner-art" aria-hidden="true"><span>◉</span><i></i><b></b></div>
    </div>
    <div class="stats-grid">
        <article class="stat-card"><span class="stat-icon teal" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4a2 2 0 0 0 1.8-3l-5-9V3"/><path d="M8 15h8M10 12h4"/></svg></span><span class="stat-label">Active materials</span><strong>{{ number_format($materialCount) }}</strong><small>Across your catalogue</small></article>
        <article class="stat-card"><span class="stat-icon blue" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="m12 2 8 4.5v10L12 21l-8-4.5v-10L12 2Z"/><path d="m4 6.5 8 4.5 8-4.5M12 11v10M8 4.2l8 4.5M8 9v3l8 4.5M8 15l8-4.5"/></svg></span><span class="stat-label">Active suppliers</span><strong>{{ number_format($supplierCount) }}</strong><small>Available for purchasing</small></article>
        <article class="stat-card"><span class="stat-icon amber" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="m12 3 10 18H2L12 3Z"/><path d="M12 9v5M12 18h.01"/></svg></span><span class="stat-label">Low stock items</span><strong>{{ number_format($lowStockCount) }}</strong><small>At or below reorder level</small></article>
        <article class="stat-card"><span class="stat-icon purple" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M3 4h2l2.5 12h11L21 8H6"/><circle cx="9" cy="21" r="1"/><circle cx="18" cy="21" r="1"/></svg></span><span class="stat-label">Draft purchases</span><strong>{{ number_format($openPurchases) }}</strong><small>Awaiting receipt into stock</small></article>
    </div>
    <div class="dashboard-grid">
        @if(auth()->user()->hasPermission('lots.view'))
        <section class="panel">
            <div class="panel-heading"><div><h2>Expiry watch</h2><p>Lots expiring in the next 90 days</p></div><a class="text-link" href="{{ route('lots.index', ['filter' => 'expiring']) }}">View all <span>→</span></a></div>
            @forelse ($expiryLots as $lot)
                @if(auth()->user()->hasPermission('materials.view'))
                    <a class="list-row" href="{{ route('materials.show', $lot->material) }}"><span class="list-icon amber-bg">⌁</span><span class="list-main"><strong>{{ $lot->material->name }}</strong><small>Lot {{ $lot->lot_number }} · {{ number_format((float) $lot->quantity_on_hand, 3) }} {{ $lot->material->stock_unit }}</small></span><span class="expiry-date">{{ $lot->expiry_date->format('d M Y') }}<small>{{ $lot->expiry_date->diffForHumans() }}</small></span></a>
                @else
                    <div class="list-row"><span class="list-icon amber-bg">⌁</span><span class="list-main"><strong>{{ $lot->material->name }}</strong><small>Lot {{ $lot->lot_number }} · {{ number_format((float) $lot->quantity_on_hand, 3) }} {{ $lot->material->stock_unit }}</small></span><span class="expiry-date">{{ $lot->expiry_date->format('d M Y') }}<small>{{ $lot->expiry_date->diffForHumans() }}</small></span></div>
                @endif
            @empty
                <div class="empty-state"><span class="empty-mark">✓</span><strong>Nothing expiring soon</strong><p>Lots inside the 90-day window will show here.</p></div>
            @endforelse
        </section>
        @endif
        @if(auth()->user()->hasPermission('materials.view'))
        <section class="panel value-panel">
            <span class="eyebrow">CURRENT STOCK VALUE</span><strong class="value-total">{{ $inventorySettings['currency_symbol'] }}{{ number_format((float) $stockValue, 2) }}</strong><p>Based on quantity on hand × recorded unit cost.</p>
            <div class="value-divider"></div><a class="button button-secondary button-wide" href="{{ route('materials.index') }}">Browse materials <span>→</span></a>
        </section>
        @endif
    </div>
    @if(auth()->user()->hasPermission('movements.view'))
    <section class="panel movement-panel">
        <div class="panel-heading"><div><h2>Recent stock movements</h2><p>Latest changes across your inventory</p></div><a class="text-link" href="{{ route('movements.index') }}">Movement history <span>→</span></a></div>
        <div class="table-wrap"><table><thead><tr><th>MATERIAL</th><th>MOVEMENT</th><th>QUANTITY</th><th>BALANCE AFTER</th><th>RECORDED</th></tr></thead><tbody>
        @forelse ($recentMovements as $movement)
            <tr><td>@if(auth()->user()->hasPermission('materials.view'))<a class="table-primary" href="{{ route('materials.show', $movement->material) }}">{{ $movement->material->name }}</a>@else{{ $movement->material->name }}@endif<small class="table-secondary">{{ $movement->material->item_code }}</small></td><td><span class="badge badge-neutral">{{ str_replace('_', ' ', $movement->movement_type) }}</span></td><td class="{{ (float) $movement->quantity < 0 ? 'text-danger' : 'text-success' }}">{{ (float) $movement->quantity > 0 ? '+' : '' }}{{ number_format((float) $movement->quantity, 3) }} {{ $movement->material->stock_unit }}</td><td>{{ $movement->balance_after === null ? '—' : number_format((float) $movement->balance_after, 3) }}</td><td>{{ $movement->created_at->format('d M, H:i') }}</td></tr>
        @empty<tr><td colspan="5"><div class="table-empty">No movements have been recorded yet.</div></td></tr>@endforelse
        </tbody></table></div>
    </section>
    @endif
@endsection
