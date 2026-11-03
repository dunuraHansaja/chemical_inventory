@extends('layouts.app')
@section('title', 'Purchases')
@section('subtitle', 'Create supplier orders with one or multiple items, then receive them into traceable stock lots.')
@section('actions')@if(auth()->user()->hasPermission('purchases.create'))<a class="button button-primary" href="{{ route('purchases.create') }}">＋ New purchase</a>@endif @endsection
@section('content')
<section class="panel"><div class="table-wrap"><table><thead><tr><th>ORDER</th><th>SUPPLIER</th><th>ORDER DATE</th><th>ITEMS</th><th>EST. TOTAL</th><th>STATUS</th><th>ACTIONS</th></tr></thead><tbody>
@forelse($orders as $order)
<?php
    $subtotal = $order->items->sum(fn($item) => (float)$item->quantity * (float)$item->unit_cost);
    $itemCount = $order->items->count();
?>
<tr>
    <td>
        <a class="table-primary" href="{{ route('purchases.show', $order) }}">{{ $order->order_number }}</a>
        <small class="table-secondary">{{ $order->invoice_number ?: 'No invoice number' }}</small>
    </td>
    <td>{{ $order->supplier->name }}</td>
    <td>{{ $order->ordered_at->format('d M Y') }}</td>
    <td>
        <div class="purchase-items-cell">
            <span class="badge badge-neutral">{{ $itemCount }} {{ $itemCount === 1 ? 'item' : 'items' }}</span>
            @if($order->items->isNotEmpty())
                <small class="table-secondary purchase-items-summary">
                    {{ $order->items->take(3)->map(fn($it) => ($it->material?->name ?? 'Item').' ('.number_format((float)$it->quantity, 2).($it->material?->stock_unit ? ' '.$it->material->stock_unit : '').')')->implode(', ') }}{{ $itemCount > 3 ? ' +' . ($itemCount - 3) . ' more' : '' }}
                </small>
            @endif
        </div>
    </td>
    <td>{{ $inventorySettings['currency_symbol'] }}{{ number_format($subtotal * (1 + (float)$order->vat_rate / 100), 2) }}</td>
    <td><span class="badge {{ $order->status === 'received' ? 'badge-success' : 'badge-warning' }}">{{ ucfirst($order->status) }}</span></td>
    <td class="actions-cell">
        <div class="goods-table-actions">
            <a class="row-action" href="{{ route('purchases.show', $order) }}">View</a>
            @if($order->status === 'draft' && auth()->user()->hasPermission('purchases.edit'))
                <a class="row-action" href="{{ route('purchases.edit', $order) }}">Edit</a>
            @endif
            @if($order->status === 'draft' && auth()->user()->hasPermission('purchases.delete'))
                <form method="POST"
                      action="{{ route('purchases.destroy', $order) }}"
                      data-confirm="Delete draft purchase order “{{ $order->order_number }}”? This action cannot be undone."
                      data-confirm-title="Delete purchase order?"
                      data-confirm-action="Delete"
                      data-confirm-tone="danger">
                    @csrf
                    @method('DELETE')
                    <button class="row-action danger-link" type="submit">Delete</button>
                </form>
            @endif
        </div>
    </td>
</tr>
@empty<tr><td colspan="7"><div class="empty-state"><span class="empty-mark">↙</span><strong>No purchases yet</strong><p>Create a draft order, then receive it to update stock.</p>@if(auth()->user()->hasPermission('purchases.create'))<a class="button button-primary" href="{{ route('purchases.create') }}">Create purchase</a>@endif</div></td></tr>@endforelse
</tbody></table></div><div class="pagination-wrap">{{ $orders->links() }}</div></section>
@endsection
