@extends('layouts.app')
@section('title', 'Materials')
@section('subtitle', 'Your chemical catalogue, stock levels and material records.')
@section('actions')
    <a class="button button-secondary" href="{{ route('materials.export') }}">↓ Export CSV</a>
    <a class="button button-primary" href="{{ route('materials.create') }}">＋ Add material</a>
@endsection
@section('content')
    <section class="panel">
        <div class="toolbar">
            <form method="GET" action="{{ route('materials.index') }}" class="search-form"><input name="q" value="{{ request('q') }}" placeholder="Search name, code or CAS…" aria-label="Search materials"><button class="button button-secondary" type="submit">Search</button></form>
            <div class="filter-tabs"><a class="{{ request('filter') ? '' : 'selected' }}" href="{{ route('materials.index') }}">All materials</a><a class="{{ request('filter') === 'low' ? 'selected' : '' }}" href="{{ route('materials.index', ['filter' => 'low']) }}">Low stock</a><a class="{{ request('filter') === 'expiry' ? 'selected' : '' }}" href="{{ route('materials.index', ['filter' => 'expiry']) }}">Expiring</a></div>
        </div>
        <div class="table-wrap"><table><thead><tr><th>MATERIAL</th><th>CATEGORY</th><th>ON HAND</th><th>REORDER AT</th><th>LOCATION</th><th>STATUS</th><th></th></tr></thead><tbody>
        @forelse ($materials as $material)
            @php($onHand = (float) ($material->lots_sum_quantity_on_hand ?? 0))
            <tr>
                <td><a class="table-primary" href="{{ route('materials.show', $material) }}">{{ $material->name }}</a><small class="table-secondary">{{ $material->item_code }} @if($material->cas_number) · CAS {{ $material->cas_number }} @endif</small></td>
                <td>{{ $material->category?->name ?? 'Uncategorized' }}</td>
                <td><strong>{{ number_format($onHand, 3) }}</strong> <small>{{ $material->stock_unit }}</small></td>
                <td>{{ number_format((float) $material->reorder_level, 3) }} {{ $material->stock_unit }}</td>
                <td>{{ $material->storage_location ?: '—' }}</td>
                <td>@if(!$material->is_active)<span class="badge badge-neutral">Inactive</span>@elseif($onHand <= (float)$material->reorder_level)<span class="badge badge-warning">Low stock</span>@else<span class="badge badge-success">In stock</span>@endif</td>
                <td><a class="row-action" href="{{ route('materials.edit', $material) }}">Edit <span>→</span></a></td>
            </tr>
        @empty<tr><td colspan="7"><div class="empty-state"><span class="empty-mark">▤</span><strong>No materials found</strong><p>Add a material or change your search.</p><a class="button button-primary" href="{{ route('materials.create') }}">Add your first material</a></div></td></tr>@endforelse
        </tbody></table></div>
        <div class="pagination-wrap">{{ $materials->links() }}</div>
    </section>
    <section class="panel import-panel"><div><span class="eyebrow">BULK CATALOGUE SETUP</span><h2>Import materials from CSV</h2><p>Required columns: <code>item_code</code>, <code>name</code>, <code>stock_unit</code>, <code>reorder_level</code>. Optional: CAS number, unit cost and storage location.</p></div><form action="{{ route('materials.import') }}" method="POST" enctype="multipart/form-data" class="inline-form">@csrf<input type="file" name="file" accept=".csv,text/csv" required><button class="button button-secondary" type="submit">Import CSV</button></form></section>
@endsection
