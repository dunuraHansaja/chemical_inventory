@extends('layouts.app')

@section('page-class', 'materials-index-page')
@section('title', 'Semi Finished Goods')
@section('eyebrow', 'INVENTORY OPERATIONS')
@section('subtitle', 'Track intermediate components, record semi-finished production runs, and manage batch stock.')
@section('actions')
    @if(auth()->user()->hasPermission('production.view'))
        <a class="button button-secondary" href="{{ route('production.index') }}">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4a2 2 0 0 0 1.8-3l-5-9V3"/><path d="M8 15h8"/></svg>
            <span>Manage Recipes</span>
        </a>
    @endif
    <button type="button" class="button button-primary production-create-action" data-open-record-production>
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <span>Record Production</span>
    </button>
@endsection

@section('content')
<section class="materials-stats" aria-label="Semi finished goods summary">
    <a class="materials-stat stat-blue" href="{{ route('semi-finished-goods.index') }}">
        <span class="materials-stat-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none"><path d="m12 2 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5"/><path d="m3 17 9 5 9-5"/></svg>
        </span>
        <span class="materials-stat-copy">
            <small>Total Semi Finished</small>
            <strong>{{ number_format($stats['total']) }}</strong>
            <small>Active intermediates</small>
        </span>
    </a>
    <a class="materials-stat stat-green" href="{{ route('semi-finished-goods.index', ['status' => 'in_stock']) }}">
        <span class="materials-stat-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none"><path d="m12 2 8 4.5v10L12 21l-8-4.5v-10L12 2Z"/><path d="m4 6.5 8 4.5 8-4.5M12 11v10"/></svg>
        </span>
        <span class="materials-stat-copy">
            <small>In Stock</small>
            <strong>{{ number_format($stats['in_stock']) }}</strong>
            <small>Semi-finished goods</small>
        </span>
    </a>
    <a class="materials-stat stat-amber" href="{{ route('semi-finished-goods.index', ['status' => 'low_stock']) }}">
        <span class="materials-stat-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none"><path d="m12 3 10 18H2L12 3Z"/><path d="M12 9v5M12 18h.01"/></svg>
        </span>
        <span class="materials-stat-copy">
            <small>Low Stock</small>
            <strong>{{ number_format($stats['low_stock']) }}</strong>
            <small>Below reorder level</small>
        </span>
    </a>
    <a class="materials-stat stat-red" href="{{ route('semi-finished-goods.index', ['status' => 'out_of_stock']) }}">
        <span class="materials-stat-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none"><path d="m12 2 9 5v10l-9 5-9-5V7l9-5Z"/><path d="m3 7 9 5 9-5M12 12v10"/></svg>
        </span>
        <span class="materials-stat-copy">
            <small>Out of Stock</small>
            <strong>{{ number_format($stats['out_of_stock']) }}</strong>
            <small>Needs production</small>
        </span>
    </a>
    <a class="materials-stat stat-purple" href="{{ route('semi-finished-goods.index', ['status' => 'expiring']) }}">
        <span class="materials-stat-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9"/><path d="M12 7v5h5"/></svg>
        </span>
        <span class="materials-stat-copy">
            <small>Expiring Soon</small>
            <strong>{{ number_format($stats['expiring']) }}</strong>
            <small>Within 3 months</small>
        </span>
    </a>
</section>

<section class="panel materials-catalog">
    <form method="GET" action="{{ route('semi-finished-goods.index') }}" class="materials-filters">
        <label class="materials-search">
            <span class="sr-only">Search semi-finished goods</span>
            <span aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg></span>
            <input name="q" value="{{ request('q') }}" placeholder="Search code, semi-finished good name, category, or location…" aria-label="Search semi-finished goods">
        </label>
        <label class="materials-select">
            <span class="sr-only">Filter by category</span>
            <span aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="5" height="5" rx="1"/><rect x="10" y="3" width="5" height="5" rx="1"/><rect x="17" y="3" width="4" height="5" rx="1"/><rect x="3" y="10" width="5" height="5" rx="1"/><rect x="10" y="10" width="5" height="5" rx="1"/><rect x="17" y="10" width="4" height="5" rx="1"/><rect x="3" y="17" width="5" height="4" rx="1"/><rect x="10" y="17" width="5" height="4" rx="1"/><rect x="17" y="17" width="4" height="4" rx="1"/></svg></span>
            <select name="category_id" aria-label="Filter by category">
                <option value="">All product categories</option>
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
            </select>
        </label>
        <input type="hidden" id="materials-page-size" name="per_page" value="{{ request('per_page', 10) }}">
        <button class="button button-secondary materials-reset" type="button" data-reset-filters data-reset-url="{{ route('semi-finished-goods.index') }}">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 11a8 8 0 1 0 2 5"/><path d="M20 4v7h-7"/></svg>
            <span>Reset</span>
        </button>
    </form>

    <div class="toolbar">
        <div class="filter-tabs">
            <a class="{{ request('status') ? '' : 'selected' }}" href="{{ route('semi-finished-goods.index', request()->except(['status', 'page'])) }}">All semi-finished goods</a>
            <a class="{{ request('status') === 'in_stock' ? 'selected' : '' }}" href="{{ route('semi-finished-goods.index', array_merge(request()->except('page'), ['status' => 'in_stock'])) }}">In stock</a>
            <a class="{{ request('status') === 'low_stock' ? 'selected' : '' }}" href="{{ route('semi-finished-goods.index', array_merge(request()->except('page'), ['status' => 'low_stock'])) }}">Low stock</a>
            <a class="{{ request('status') === 'out_of_stock' ? 'selected' : '' }}" href="{{ route('semi-finished-goods.index', array_merge(request()->except('page'), ['status' => 'out_of_stock'])) }}">Out of stock</a>
            <a class="{{ request('status') === 'expiring' ? 'selected' : '' }}" href="{{ route('semi-finished-goods.index', array_merge(request()->except('page'), ['status' => 'expiring'])) }}">Expiring soon</a>
        </div>
        <span class="result-count">{{ number_format($products->total()) }} {{ \Illuminate\Support\Str::plural('semi-finished good', $products->total()) }}</span>
    </div>

    <div class="table-wrap materials-table-wrap">
        <table class="materials-table goods-module-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Semi Finished Good</th>
                    <th>Classification</th>
                    <th>Category</th>
                    <th>On Hand</th>
                    <th>Reorder At</th>
                    <th>Next Expiry</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($products as $product)
                <?php
                    $onHand = (float) ($product->lots_sum_quantity_on_hand ?? 0);
                    $isExpiring = $product->next_expiry_date && \Illuminate\Support\Carbon::parse($product->next_expiry_date)->lte(today()->addDays(90));
                    $status = ! $product->is_active
                        ? 'Inactive'
                        : ($onHand <= 0 ? 'Out of stock' : ($onHand <= (float) $product->reorder_level ? 'Low stock' : 'In stock'));
                    $categoryName = $product->productCategory?->name ?? $product->category?->name ?? 'Uncategorized';
                ?>
                <tr>
                    <td data-label="Code">
                        <a class="table-primary" href="{{ route('materials.show', $product) }}">{{ $product->item_code }}</a>
                    </td>
                    <td data-label="Semi Finished Good">
                        <a class="table-primary" href="{{ route('materials.show', $product) }}">{{ $product->name }}</a>
                        <small class="table-secondary">1.00 {{ $product->stock_unit }}</small>
                    </td>
                    <td data-label="Classification">
                        <span class="production-classification-badge is-semi-finished">
                            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 2 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5"/></svg>
                            <span>Semi Finished Good</span>
                        </span>
                    </td>
                    <td data-label="Category">
                        <span class="materials-category">{{ $categoryName }}</span>
                    </td>
                    <td data-label="On Hand">
                        <strong>{{ number_format($onHand, 2) }}</strong> {{ $product->stock_unit }}
                    </td>
                    <td data-label="Reorder At">
                        {{ number_format((float) $product->reorder_level, 2) }} {{ $product->stock_unit }}
                    </td>
                    <td data-label="Next Expiry">
                        @if($product->next_expiry_date)
                            <span class="{{ $isExpiring ? 'materials-expiry-soon' : '' }}">{{ \Illuminate\Support\Carbon::parse($product->next_expiry_date)->format('Y-m-d') }}</span>
                        @else
                            —
                        @endif
                    </td>
                    <td data-label="Location">
                        {{ $product->storage_location ?: $product->effective_storage_location ?: '—' }}
                    </td>
                    <td data-label="Status">
                        <span class="badge {{ $status === 'In stock' ? 'badge-success' : ($status === 'Low stock' ? 'badge-warning' : ($status === 'Out of stock' ? 'badge-danger' : 'badge-neutral')) }}">
                            {{ $status }}
                        </span>
                    </td>
                    <td data-label="Actions" class="actions-cell">
                        <div class="goods-table-actions">
                            <button type="button"
                                    class="goods-produce-row-btn"
                                    data-produce-product-id="{{ $product->id }}"
                                    title="Record production for {{ $product->name }}">
                                ＋ Produce
                            </button>
                            @if(auth()->user()->hasPermission('production.view'))
                                <a class="row-action" href="{{ route('production.index', ['edit' => $product->id]) }}" title="Manage in Products">
                                    Edit
                                </a>
                            @endif
                            @if(auth()->user()->hasPermission('production.create'))
                                <form method="POST"
                                      action="{{ route('production.destroy', $product) }}"
                                      data-confirm="Delete “{{ $product->name }}” and its formulation recipe? This action cannot be undone."
                                      data-confirm-title="Delete semi-finished good?"
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
            @empty
                <tr>
                    <td colspan="10">
                        <div class="empty-state">
                            <span class="empty-mark">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5"/><path d="m3 17 9 5 9-5"/></svg>
                            </span>
                            <strong>No Semi Finished Goods found</strong>
                            <p>Products classified as <strong>Semi Finished Good</strong> in the Products module will automatically appear here.</p>
                            @if(auth()->user()->hasPermission('production.create'))
                                <a class="button button-primary" href="{{ route('production.create') }}">＋ Add Semi Finished Good in Products</a>
                            @endif
                        </div>
                    </td>
                </tr>
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
        <div class="pagination-wrap">{{ $products->onEachSide(2)->links() }}</div>
    </div>
</section>

@include('partials.record-production-drawer', [
    'moduleLabel' => 'SEMI FINISHED GOODS PRODUCTION',
    'produceRoute' => route('semi-finished-goods.produce'),
    'predefinedProducts' => $predefinedProducts,
    'storageLocations' => $storageLocations,
    'defaultBatchNo' => $defaultBatchNo,
])
@endsection
