@extends('layouts.app')
@section('page-class', 'materials-index-page')
@section('title', 'Materials')
@section('subtitle', 'Your chemical catalogue, stock levels and material records.')
@section('actions')
    @if(auth()->user()->hasPermission('materials.create'))<a class="button button-primary" href="{{ route('materials.create') }}">＋ Add material</a>@endif
@endsection
@section('content')
    <section class="materials-stats" aria-label="Material stock summary">
        <a class="materials-stat stat-blue" href="{{ route('materials.index') }}">
            <span class="materials-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4a2 2 0 0 0 1.8-3l-5-9V3"/><path d="M8 15h8M10 12h4"/></svg></span>
            <span class="materials-stat-copy"><small>Total Materials</small><strong>{{ number_format($stats['total']) }}</strong><small>Active materials</small></span>
        </a>
        <a class="materials-stat stat-green" href="{{ route('materials.index', ['status' => 'in_stock']) }}">
            <span class="materials-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="m12 2 8 4.5v10L12 21l-8-4.5v-10L12 2Z"/><path d="m4 6.5 8 4.5 8-4.5M12 11v10M8 4.2l8 4.5M8 9v3l8 4.5M8 15l8-4.5"/></svg></span>
            <span class="materials-stat-copy"><small>In Stock</small><strong>{{ number_format($stats['in_stock']) }}</strong><small>Materials</small></span>
        </a>
        <a class="materials-stat stat-amber" href="{{ route('materials.index', ['status' => 'low_stock']) }}">
            <span class="materials-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="m12 3 10 18H2L12 3Z"/><path d="M12 9v5M12 18h.01"/></svg></span>
            <span class="materials-stat-copy"><small>Low Stock</small><strong>{{ number_format($stats['low_stock']) }}</strong><small>Materials</small></span>
        </a>
        <a class="materials-stat stat-red" href="{{ route('materials.index', ['status' => 'out_of_stock']) }}">
            <span class="materials-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="m12 2 9 5v10l-9 5-9-5V7l9-5Z"/><path d="m3 7 9 5 9-5M12 12v10"/></svg></span>
            <span class="materials-stat-copy"><small>Out of Stock</small><strong>{{ number_format($stats['out_of_stock']) }}</strong><small>Materials</small></span>
        </a>
        <a class="materials-stat stat-purple" href="{{ route('materials.index', ['status' => 'expiring']) }}">
            <span class="materials-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9"/><path d="M12 7v5h5"/></svg></span>
            <span class="materials-stat-copy"><small>Expiring Soon</small><strong>{{ number_format($stats['expiring']) }}</strong><small>Within 3 months</small></span>
        </a>
    </section>

    <section class="panel materials-catalog">
        <form method="GET" action="{{ route('materials.index') }}" class="materials-filters">
            <label class="materials-search">
                <span class="sr-only">Search materials</span>
                <span aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg></span>
                <input name="q" value="{{ request('q') }}" placeholder="Search code, name, CAS no., location or supplier…" aria-label="Search code, name, CAS number, location or supplier">
            </label>
            <label class="materials-select">
                <span class="sr-only">Filter by category</span>
                <span aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="5" height="5" rx="1"/><rect x="10" y="3" width="5" height="5" rx="1"/><rect x="17" y="3" width="4" height="5" rx="1"/><rect x="3" y="10" width="5" height="5" rx="1"/><rect x="10" y="10" width="5" height="5" rx="1"/><rect x="17" y="10" width="4" height="5" rx="1"/><rect x="3" y="17" width="5" height="4" rx="1"/><rect x="10" y="17" width="5" height="4" rx="1"/><rect x="17" y="17" width="4" height="4" rx="1"/></svg></span>
                <select name="category_id" aria-label="Filter by category">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="materials-select">
                <span class="sr-only">Filter by status</span>
                <span aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="m12 3 9 9-9 9-9-9 9-9Z"/></svg></span>
                <select name="status" aria-label="Filter by status">
                    <option value="">Any status</option>
                    <option value="in_stock" @selected(request('status') === 'in_stock')>In stock</option>
                    <option value="low_stock" @selected(request('status') === 'low_stock')>Low stock</option>
                    <option value="out_of_stock" @selected(request('status') === 'out_of_stock')>Out of stock</option>
                    <option value="expiring" @selected(request('status') === 'expiring')>Expiring soon</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </label>
            <input type="hidden" name="sort" value="{{ request('sort', 'material') }}">
            <input type="hidden" name="direction" value="{{ request('direction', 'asc') }}">
            <input type="hidden" id="materials-page-size" name="per_page" value="{{ request('per_page', 10) }}">
            <button class="button button-secondary materials-reset" type="button" data-reset-filters data-reset-url="{{ route('materials.index') }}"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 11a8 8 0 1 0 2 5"/><path d="M20 4v7h-7"/></svg><span>Reset</span></button>
        </form>

        <div class="toolbar">
            <div class="filter-tabs">
                <a class="{{ request('status') ? '' : 'selected' }}" href="{{ route('materials.index') }}">All materials</a>
                <a class="{{ request('status') === 'in_stock' ? 'selected' : '' }}" href="{{ route('materials.index', ['status' => 'in_stock']) }}">In stock</a>
                <a class="{{ request('status') === 'low_stock' ? 'selected' : '' }}" href="{{ route('materials.index', ['status' => 'low_stock']) }}">Low stock</a>
                <a class="{{ request('status') === 'out_of_stock' ? 'selected' : '' }}" href="{{ route('materials.index', ['status' => 'out_of_stock']) }}">Out of stock</a>
                <a class="{{ request('status') === 'expiring' ? 'selected' : '' }}" href="{{ route('materials.index', ['status' => 'expiring']) }}">Expiring soon</a>
            </div>
            <span class="result-count">{{ number_format($materials->total()) }} materials</span>
        </div>

        <div class="table-wrap materials-table-wrap">
            <table class="materials-table">
                <thead>
                    <tr>
                        @php($columns = ['Code', 'Material', 'CAS no.', 'Category', 'On hand', 'Reorder at', 'Next expiry', 'Location', 'Status'])
                        @foreach($columns as $label)
                            <th>{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @forelse ($materials as $material)
                    @php($onHand = (float) ($material->lots_sum_quantity_on_hand ?? 0))
                    @php($isExpiring = $material->next_expiry_date && \Illuminate\Support\Carbon::parse($material->next_expiry_date)->lte(today()->addDays(90)))
                    @php($status = !$material->is_active ? 'Inactive' : ($onHand <= 0 ? 'Out of stock' : ($onHand <= (float) $material->reorder_level ? 'Low stock' : 'In stock')))
                    <tr>
                        <td data-label="Code"><a class="table-primary" href="{{ route('materials.show', $material) }}">{{ $material->item_code }}</a></td>
                        <td data-label="Material"><a class="table-primary" href="{{ route('materials.show', $material) }}">{{ $material->name }}</a><small class="table-secondary">{{ $material->stock_unit }}</small></td>
                        <td data-label="CAS No.">{{ $material->cas_number ?: '—' }}</td>
                        <td data-label="Category"><span class="materials-category">{{ $material->category?->name ?? 'Uncategorized' }}</span></td>
                        <td data-label="On Hand"><strong>{{ number_format($onHand, 3) }}</strong> {{ $material->stock_unit }}</td>
                        <td data-label="Reorder At">{{ number_format((float) $material->reorder_level, 3) }} {{ $material->stock_unit }}</td>
                        <td data-label="Next Expiry">
                            @if($material->next_expiry_date)
                                <span class="{{ $isExpiring ? 'materials-expiry-soon' : '' }}">{{ \Illuminate\Support\Carbon::parse($material->next_expiry_date)->format('Y-m-d') }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td data-label="Location">{{ $material->storage_location ?: $material->effective_storage_location ?: '—' }}</td>
                        <td data-label="Status"><span class="badge {{ $status === 'In stock' ? 'badge-success' : ($status === 'Low stock' ? 'badge-warning' : ($status === 'Out of stock' ? 'badge-danger' : 'badge-neutral')) }}">{{ $status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="9"><div class="empty-state"><span class="empty-mark">▤</span><strong>No materials found</strong><p>Add a material or change your search.</p>@if(auth()->user()->hasPermission('materials.create'))<a class="button button-primary" href="{{ route('materials.create') }}">Add your first material</a>@endif</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="materials-pagination">
            <label for="materials-page-size-bottom">Rows per page</label>
            <select id="materials-page-size-bottom" aria-label="Rows per page" data-page-size>
                @foreach([10, 25, 50, 100] as $pageSize)
                    <option value="{{ $pageSize }}" @selected((int) request('per_page', 10) === $pageSize)>{{ $pageSize }}</option>
                @endforeach
            </select>
            <div class="pagination-wrap">{{ $materials->onEachSide(2)->links() }}</div>
        </div>
    </section>
@endsection
