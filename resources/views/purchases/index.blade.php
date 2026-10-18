@extends('layouts.app')
@section('title', 'Purchases')
@section('subtitle', 'Create supplier orders, then receive them into traceable stock lots.')
@section('actions')@if(auth()->user()->hasPermission('purchases.create'))<a class="button button-primary" href="{{ route('purchases.create') }}">＋ New purchase</a>@endif @endsection
@section('content')
<section class="panel"><div class="table-wrap"><table><thead><tr><th>ORDER</th><th>SUPPLIER</th><th>ORDER DATE</th><th>ITEMS</th><th>EST. TOTAL</th><th>STATUS</th><th></th></tr></thead><tbody>
@forelse($orders as $order)
@php($subtotal = $order->items->sum(fn($item) => (float)$item->quantity * (float)$item->unit_cost))
<tr><td><a class="table-primary" href="{{ route('purchases.show', $order) }}">{{ $order->order_number }}</a><small class="table-secondary">{{ $order->invoice_number ?: 'No invoice number' }}</small></td><td>{{ $order->supplier->name }}</td><td>{{ $order->ordered_at->format('d M Y') }}</td><td>{{ $order->items->count() }}</td><td>{{ $inventorySettings['currency_symbol'] }}{{ number_format($subtotal * (1 + (float)$order->vat_rate / 100), 2) }}</td><td><span class="badge {{ $order->status === 'received' ? 'badge-success' : 'badge-warning' }}">{{ ucfirst($order->status) }}</span></td><td><a class="row-action" href="{{ route('purchases.show', $order) }}">View <span>→</span></a></td></tr>
@empty<tr><td colspan="7"><div class="empty-state"><span class="empty-mark">↙</span><strong>No purchases yet</strong><p>Create a draft order, then receive it to update stock.</p>@if(auth()->user()->hasPermission('purchases.create'))<a class="button button-primary" href="{{ route('purchases.create') }}">Create purchase</a>@endif</div></td></tr>@endforelse
</tbody></table></div><div class="pagination-wrap">{{ $orders->links() }}</div></section>
@endsection
