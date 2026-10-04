@extends('layouts.app')
@section('title', 'Production')
@section('subtitle', 'Record production runs with lot-traced input consumption and finished material output.')
@section('actions')<a class="button button-primary" href="{{ route('production.create') }}">＋ Record production</a>@endsection
@section('content')
<section class="panel"><div class="table-wrap"><table><thead><tr><th>RUN</th><th>PRODUCED</th><th>INPUTS CONSUMED</th><th>OUTPUTS CREATED</th><th>STATUS</th><th>RECORDED BY</th></tr></thead><tbody>
@forelse($orders as $order)
<tr><td><strong class="table-primary">{{ $order->order_number }}</strong><small class="table-secondary">{{ $order->notes ?: 'Production run' }}</small></td><td>{{ $order->produced_at->format('d M Y') }}</td><td>@foreach($order->items->where('direction', 'input') as $item)<div class="compact-list">{{ $item->material->name }} <span>{{ number_format((float)$item->quantity, 3) }} {{ $item->material->stock_unit }}</span></div>@endforeach</td><td>@foreach($order->items->where('direction', 'output') as $item)<div class="compact-list">{{ $item->material->name }} <span>+{{ number_format((float)$item->quantity, 3) }} {{ $item->material->stock_unit }}</span></div>@endforeach</td><td><span class="badge badge-success">Completed</span></td><td>{{ $order->createdBy?->name ?? 'System' }}</td></tr>
@empty<tr><td colspan="6"><div class="empty-state"><span class="empty-mark">⚙</span><strong>No production runs yet</strong><p>Record a run to consume materials and add output stock.</p><a class="button button-primary" href="{{ route('production.create') }}">Record production</a></div></td></tr>@endforelse
</tbody></table></div><div class="pagination-wrap">{{ $orders->links() }}</div></section>
@endsection
