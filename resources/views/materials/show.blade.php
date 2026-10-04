@extends('layouts.app')
@section('title', $material->name)
@section('subtitle', 'Material record · '.$material->item_code)
@section('actions')
    <a class="button button-secondary" href="{{ route('materials.edit', $material) }}">Edit material</a>
    <a class="button button-primary" href="{{ route('lots.create', ['material_id' => $material->id]) }}">＋ Receive lot</a>
@endsection
@section('content')
    @php($onHand = (float) $material->lots()->sum('quantity_on_hand'))
    <div class="detail-stats">
        <div class="detail-stat"><span>ON HAND</span><strong>{{ number_format($onHand, 3) }} <small>{{ $material->stock_unit }}</small></strong><p>Across {{ $material->lots()->count() }} lots</p></div>
        <div class="detail-stat"><span>REORDER LEVEL</span><strong>{{ number_format((float) $material->reorder_level, 3) }} <small>{{ $material->stock_unit }}</small></strong><p>{{ $onHand <= (float)$material->reorder_level ? 'Reorder recommended' : 'Stock above threshold' }}</p></div>
        <div class="detail-stat"><span>STATUS</span><strong><span class="badge {{ $material->is_active ? 'badge-success' : 'badge-neutral' }}">{{ $material->is_active ? 'Active' : 'Inactive' }}</span></strong><p>Updated {{ $material->updated_at->diffForHumans() }}</p></div>
    </div>
    <div class="detail-grid"><section class="panel"><div class="panel-heading"><div><h2>Material information</h2><p>Catalogue and handling details</p></div></div><dl class="detail-list">
        <div><dt>Item code</dt><dd>{{ $material->item_code }}</dd></div><div><dt>CAS number</dt><dd>{{ $material->cas_number ?: '—' }}</dd></div><div><dt>Category</dt><dd>{{ $material->category?->name ?? 'Uncategorized' }}</dd></div><div><dt>Preferred supplier</dt><dd>{{ $material->preferredSupplier?->name ?? '—' }}</dd></div><div><dt>Storage location</dt><dd>{{ $material->storage_location ?: '—' }}</dd></div><div><dt>Cost per unit</dt><dd>{{ $material->cost_per_unit === null ? '—' : $inventorySettings['currency_symbol'].number_format((float)$material->cost_per_unit, 2) }}</dd></div><div><dt>Hazard classes</dt><dd>{{ implode(', ', $material->hazard_classes ?? []) ?: '—' }}</dd></div><div><dt>Handling notes</dt><dd>{{ $material->handling_notes ?: '—' }}</dd></div>
    </dl></section><section class="panel quick-panel"><span class="eyebrow">QUICK ACTIONS</span><h2>Keep stock current</h2><p>Record a new lot when material arrives, or make a documented stock correction.</p><a class="button button-primary button-wide" href="{{ route('lots.create', ['material_id' => $material->id]) }}">＋ Receive new lot</a><a class="button button-secondary button-wide" href="{{ route('adjustments.create') }}">± Adjust a lot</a><form method="POST" action="{{ route('materials.destroy', $material) }}" onsubmit="return confirm('Archive this material?')">@csrf @method('DELETE')<button class="button button-danger button-wide" type="submit">Archive material</button></form></section></div>
    <section class="panel"><div class="panel-heading"><div><h2>Lots &amp; batches</h2><p>Expiry dates and quantities by lot</p></div><a class="text-link" href="{{ route('lots.index') }}">All lots <span>→</span></a></div><div class="table-wrap"><table><thead><tr><th>LOT NUMBER</th><th>MANUFACTURED</th><th>EXPIRES</th><th>ON HAND</th><th>UNIT COST</th><th>STATUS</th></tr></thead><tbody>
        @forelse($lots as $lot)<tr><td><strong>{{ $lot->lot_number }}</strong><small class="table-secondary">{{ $lot->supplier_lot_number ? 'Supplier lot '.$lot->supplier_lot_number : '' }}</small></td><td>{{ $lot->manufactured_date?->format('d M Y') ?? '—' }}</td><td>{{ $lot->expiry_date?->format('d M Y') ?? 'No expiry' }}</td><td>{{ number_format((float)$lot->quantity_on_hand, 3) }} {{ $material->stock_unit }}</td><td>{{ $lot->unit_cost === null ? '—' : $inventorySettings['currency_symbol'].number_format((float)$lot->unit_cost, 2) }}</td><td><span class="badge {{ (float)$lot->quantity_on_hand > 0 ? 'badge-success' : 'badge-neutral' }}">{{ (float)$lot->quantity_on_hand > 0 ? 'Available' : 'Depleted' }}</span></td></tr>
        @empty<tr><td colspan="6"><div class="table-empty">No lots recorded for this material.</div></td></tr>@endforelse
    </tbody></table></div><div class="pagination-wrap">{{ $lots->links() }}</div></section>
@endsection
