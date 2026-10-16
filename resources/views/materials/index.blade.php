@extends('layouts.app')
@section('page-class', 'materials-index-page')
@section('title', 'Materials')
@section('subtitle', 'Your chemical catalogue, stock levels and material records.')
@section('actions')
    <a class="button button-primary" href="{{ route('materials.create') }}">＋ Add material</a>
@endsection
@section('content')
    <section class="materials-stats" aria-label="Material stock summary">
        <a class="materials-stat stat-blue" href="{{ route('materials.index') }}">
            <span class="materials-stat-icon" aria-hidden="true">⚗</span>
            <span class="materials-stat-copy"><small>Total Materials</small><strong>{{ number_format($stats['total']) }}</strong><small>Active materials</small></span>
        </a>
        <a class="materials-stat stat-green" href="{{ route('materials.index', ['status' => 'in_stock']) }}">
            <span class="materials-stat-icon" aria-hidden="true">▣</span>
            <span class="materials-stat-copy"><small>In Stock</small><strong>{{ number_format($stats['in_stock']) }}</strong><small>Materials</small></span>
        </a>
        <a class="materials-stat stat-amber" href="{{ route('materials.index', ['status' => 'low_stock']) }}">
            <span class="materials-stat-icon" aria-hidden="true">△</span>
            <span class="materials-stat-copy"><small>Low Stock</small><strong>{{ number_format($stats['low_stock']) }}</strong><small>Materials</small></span>
        </a>
        <a class="materials-stat stat-red" href="{{ route('materials.index', ['status' => 'out_of_stock']) }}">
            <span class="materials-stat-icon" aria-hidden="true">⊗</span>
            <span class="materials-stat-copy"><small>Out of Stock</small><strong>{{ number_format($stats['out_of_stock']) }}</strong><small>Materials</small></span>
        </a>
        <a class="materials-stat stat-purple" href="{{ route('materials.index', ['status' => 'expiring']) }}">
            <span class="materials-stat-icon" aria-hidden="true">◷</span>
            <span class="materials-stat-copy"><small>Expiring Soon</small><strong>{{ number_format($stats['expiring']) }}</strong><small>Within 3 months</small></span>
        </a>
    </section>

    <section class="panel materials-catalog">
        <form method="GET" action="{{ route('materials.index') }}" class="materials-filters">
            <label class="materials-search">
                <span class="sr-only">Search materials</span>
                <span aria-hidden="true">⌕</span>
                <input name="q" value="{{ request('q') }}" placeholder="Search code, name, CAS no., location or supplier…" aria-label="Search code, name, CAS number, location or supplier">
            </label>
            <label class="materials-select">
                <span class="sr-only">Filter by category</span>
                <span aria-hidden="true">▦</span>
                <select name="category_id" aria-label="Filter by category">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="materials-select">
                <span class="sr-only">Filter by status</span>
                <span aria-hidden="true">◇</span>
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
            <button class="button button-secondary materials-reset" type="button" data-reset-filters data-reset-url="{{ route('materials.index') }}">↻ <span>Reset</span></button>
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
                        @php($columns = ['code' => 'Code', 'material' => 'Material', 'cas' => 'CAS no.', 'category' => 'Category', 'on_hand' => 'On hand', 'reorder' => 'Reorder at', 'expiry' => 'Next expiry', 'location' => 'Location', 'status' => 'Status'])
                        @foreach($columns as $key => $label)
                            <th aria-sort="{{ request('sort', 'material') === $key ? (request('direction', 'asc') === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                <a class="materials-sort-link" href="{{ request()->fullUrlWithQuery(['sort' => $key, 'direction' => request('sort') === $key && request('direction', 'asc') === 'asc' ? 'desc' : 'asc', 'page' => 1]) }}">{{ $label }} <span aria-hidden="true">{{ request('sort') === $key ? (request('direction', 'asc') === 'asc' ? '↑' : '↓') : '↕' }}</span></a>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @forelse ($materials as $material)
                    @php($onHand = (float) ($material->lots_sum_quantity_on_hand ?? 0))
                    @php($isExpiring = $material->next_expiry_date && \Illuminate\Support\Carbon::parse($material->next_expiry_date)->lte(today()->addDays(90)))
                    @php($status = !$material->is_active ? 'Inactive' : ($onHand <= 0 ? 'Out of stock' : ($onHand <= (float) $material->reorder_level ? 'Low stock' : 'In stock')))
                    <tr>
                        <td><a class="table-primary" href="{{ route('materials.show', $material) }}">{{ $material->item_code }}</a></td>
                        <td><a class="table-primary" href="{{ route('materials.show', $material) }}">{{ $material->name }}</a><small class="table-secondary">{{ $material->stock_unit }}</small></td>
                        <td>{{ $material->cas_number ?: '—' }}</td>
                        <td><span class="materials-category">{{ $material->category?->name ?? 'Uncategorized' }}</span></td>
                        <td><strong>{{ number_format($onHand, 3) }}</strong> {{ $material->stock_unit }}</td>
                        <td>{{ number_format((float) $material->reorder_level, 3) }} {{ $material->stock_unit }}</td>
                        <td>
                            @if($material->next_expiry_date)
                                <span class="{{ $isExpiring ? 'materials-expiry-soon' : '' }}">{{ \Illuminate\Support\Carbon::parse($material->next_expiry_date)->format('Y-m-d') }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $material->storage_location ?: $material->effective_storage_location ?: '—' }}</td>
                        <td><span class="badge {{ $status === 'In stock' ? 'badge-success' : ($status === 'Low stock' ? 'badge-warning' : ($status === 'Out of stock' ? 'badge-danger' : 'badge-neutral')) }}">{{ $status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="9"><div class="empty-state"><span class="empty-mark">▤</span><strong>No materials found</strong><p>Add a material or change your search.</p><a class="button button-primary" href="{{ route('materials.create') }}">Add your first material</a></div></td></tr>
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
