@extends('layouts.app')
@section('page-class', 'materials-index-page')
@section('title', $currentType === 'product' ? 'Production Products' : ($currentType === 'material' ? 'Materials' : 'All Catalogue'))
@section('subtitle', $currentType === 'product' ? 'Your manufactured goods, formulations, and batch inventory records.' : ($currentType === 'material' ? 'Your chemical catalogue, raw materials and stock levels.' : 'Comprehensive catalogue of raw chemical materials and manufactured production products.'))
@section('actions')
    @if(auth()->user()->hasPermission('materials.create'))
        @if($currentType === 'product')
            <a class="button button-primary" href="{{ route('materials.create', ['type' => 'product']) }}">＋ Add product</a>
        @else
            <a class="button button-primary" href="{{ route('materials.create', ['type' => 'material']) }}">＋ Add material</a>
        @endif
    @endif
@endsection
@section('content')
    <section class="materials-stats" aria-label="Material stock summary">
        <a class="materials-stat stat-blue" href="{{ route('materials.index', ['type' => $currentType]) }}">
            <span class="materials-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4a2 2 0 0 0 1.8-3l-5-9V3"/><path d="M8 15h8M10 12h4"/></svg></span>
            <span class="materials-stat-copy">
                <small>Total {{ $currentType === 'product' ? 'Products' : 'Materials' }}</small>
                <strong>{{ number_format($stats['total']) }}</strong>
                <small>{{ $currentType === 'product' ? 'Active products' : ($currentType === 'material' ? 'Active materials' : 'Active items') }}</small>
            </span>
        </a>
        <a class="materials-stat stat-green" href="{{ route('materials.index', ['type' => $currentType, 'status' => 'in_stock']) }}">
            <span class="materials-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="m12 2 8 4.5v10L12 21l-8-4.5v-10L12 2Z"/><path d="m4 6.5 8 4.5 8-4.5M12 11v10M8 4.2l8 4.5M8 9v3l8 4.5M8 15l8-4.5"/></svg></span>
            <span class="materials-stat-copy">
                <small>In Stock</small>
                <strong>{{ number_format($stats['in_stock']) }}</strong>
                <small>{{ $currentType === 'product' ? 'Products' : 'Materials' }}</small>
            </span>
        </a>
        <a class="materials-stat stat-amber" href="{{ route('materials.index', ['type' => $currentType, 'status' => 'low_stock']) }}">
            <span class="materials-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="m12 3 10 18H2L12 3Z"/><path d="M12 9v5M12 18h.01"/></svg></span>
            <span class="materials-stat-copy">
                <small>Low Stock</small>
                <strong>{{ number_format($stats['low_stock']) }}</strong>
                <small>{{ $currentType === 'product' ? 'Products' : 'Materials' }}</small>
            </span>
        </a>
        <a class="materials-stat stat-red" href="{{ route('materials.index', ['type' => $currentType, 'status' => 'out_of_stock']) }}">
            <span class="materials-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="m12 2 9 5v10l-9 5-9-5V7l9-5Z"/><path d="m3 7 9 5 9-5M12 12v10"/></svg></span>
            <span class="materials-stat-copy">
                <small>Out of Stock</small>
                <strong>{{ number_format($stats['out_of_stock']) }}</strong>
                <small>{{ $currentType === 'product' ? 'Products' : 'Materials' }}</small>
            </span>
        </a>
        <a class="materials-stat stat-purple" href="{{ route('materials.index', ['type' => $currentType, 'status' => 'expiring']) }}">
            <span class="materials-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9"/><path d="M12 7v5h5"/></svg></span>
            <span class="materials-stat-copy">
                <small>Expiring Soon</small>
                <strong>{{ number_format($stats['expiring']) }}</strong>
                <small>Within 3 months</small>
            </span>
        </a>
    </section>

    <!-- Category / Type Separation Switcher -->
    <div class="materials-type-switcher" role="navigation" aria-label="Catalogue type navigation">
        <a class="type-pill {{ $currentType === 'material' ? 'active' : '' }}" href="{{ route('materials.index', array_merge(request()->except(['page', 'category_id']), ['type' => 'material'])) }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4a2 2 0 0 0 1.8-3l-5-9V3"/><path d="M8 15h8"/></svg>
            <span>Raw Materials</span>
            <span class="type-badge">{{ number_format($typeCounts['material']) }}</span>
        </a>
        <a class="type-pill {{ $currentType === 'product' ? 'active' : '' }}" href="{{ route('materials.index', array_merge(request()->except(['page', 'category_id']), ['type' => 'product'])) }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 2 8 4.5v10L12 21l-8-4.5v-10L12 2Z"/><path d="m4 6.5 8 4.5 8-4.5M12 11v10"/></svg>
            <span>Production Products</span>
            <span class="type-badge">{{ number_format($typeCounts['product']) }}</span>
        </a>
        <a class="type-pill {{ $currentType === 'all' ? 'active' : '' }}" href="{{ route('materials.index', array_merge(request()->except(['page', 'category_id']), ['type' => 'all'])) }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
            <span>All Catalogue</span>
            <span class="type-badge">{{ number_format($typeCounts['all']) }}</span>
        </a>
    </div>

    <section class="panel materials-catalog">
        <form method="GET" action="{{ route('materials.index') }}" class="materials-filters">
            <input type="hidden" name="type" value="{{ $currentType }}">
            <label class="materials-search">
                <span class="sr-only">Search {{ $currentType === 'product' ? 'products' : 'materials' }}</span>
                <span aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg></span>
                <input name="q" value="{{ request('q') }}" placeholder="{{ $currentType === 'product' ? 'Search product code, name, category, or location...' : 'Search code, name, CAS no., location or supplier…' }}" aria-label="Search code, name, CAS number, location or supplier">
            </label>
            <label class="materials-select">
                <span class="sr-only">Filter by category</span>
                <span aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="5" height="5" rx="1"/><rect x="10" y="3" width="5" height="5" rx="1"/><rect x="17" y="3" width="4" height="5" rx="1"/><rect x="3" y="10" width="5" height="5" rx="1"/><rect x="10" y="10" width="5" height="5" rx="1"/><rect x="17" y="10" width="4" height="5" rx="1"/><rect x="3" y="17" width="5" height="4" rx="1"/><rect x="10" y="17" width="5" height="4" rx="1"/><rect x="17" y="17" width="4" height="4" rx="1"/></svg></span>
                <select name="category_id" aria-label="Filter by category">
                    <option value="">{{ $currentType === 'product' ? 'All product categories' : 'All categories' }}</option>
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
            <button class="button button-secondary materials-reset" type="button" data-reset-filters data-reset-url="{{ route('materials.index', ['type' => $currentType]) }}"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 11a8 8 0 1 0 2 5"/><path d="M20 4v7h-7"/></svg><span>Reset</span></button>
        </form>

        <div class="toolbar">
            <div class="filter-tabs">
                <a class="{{ request('status') ? '' : 'selected' }}" href="{{ route('materials.index', array_merge(request()->except(['status', 'page']), ['type' => $currentType])) }}">
                    {{ $currentType === 'product' ? 'All products' : ($currentType === 'material' ? 'All materials' : 'All items') }}
                </a>
                <a class="{{ request('status') === 'in_stock' ? 'selected' : '' }}" href="{{ route('materials.index', array_merge(request()->except('page'), ['type' => $currentType, 'status' => 'in_stock'])) }}">In stock</a>
                <a class="{{ request('status') === 'low_stock' ? 'selected' : '' }}" href="{{ route('materials.index', array_merge(request()->except('page'), ['type' => $currentType, 'status' => 'low_stock'])) }}">Low stock</a>
                <a class="{{ request('status') === 'out_of_stock' ? 'selected' : '' }}" href="{{ route('materials.index', array_merge(request()->except('page'), ['type' => $currentType, 'status' => 'out_of_stock'])) }}">Out of stock</a>
                <a class="{{ request('status') === 'expiring' ? 'selected' : '' }}" href="{{ route('materials.index', array_merge(request()->except('page'), ['type' => $currentType, 'status' => 'expiring'])) }}">Expiring soon</a>
            </div>
            <span class="result-count">{{ number_format($materials->total()) }} {{ $currentType === 'product' ? 'products' : 'materials' }}</span>
        </div>

        <div class="table-wrap materials-table-wrap">
            <table class="materials-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        @if($currentType === 'all')
                            <th>Type</th>
                        @endif
                        <th>{{ $currentType === 'product' ? 'Product' : 'Material' }}</th>
                        <th>{{ $currentType === 'product' ? 'Recipe / Formulation' : 'CAS no.' }}</th>
                        <th>Category</th>
                        <th>On hand</th>
                        <th>Reorder at</th>
                        <th>Next expiry</th>
                        <th>Location</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($materials as $material)
                    @php($onHand = (float) ($material->lots_sum_quantity_on_hand ?? 0))
                    @php($isExpiring = $material->next_expiry_date && \Illuminate\Support\Carbon::parse($material->next_expiry_date)->lte(today()->addDays(90)))
                    @php($status = !$material->is_active ? 'Inactive' : ($onHand <= 0 ? 'Out of stock' : ($onHand <= (float) $material->reorder_level ? 'Low stock' : 'In stock')))
                    @php($isProd = $material->isProduct())
                    <tr>
                        <td data-label="Code"><a class="table-primary" href="{{ route('materials.show', $material) }}">{{ $material->item_code }}</a></td>
                        @if($currentType === 'all')
                            <td data-label="Type">
                                <span class="type-indicator-badge {{ $isProd ? 'product-type' : 'material-type' }}">
                                    {{ $isProd ? '📦 Product' : '🧪 Material' }}
                                </span>
                            </td>
                        @endif
                        <td data-label="Material">
                            <a class="table-primary" href="{{ route('materials.show', $material) }}">{{ $material->name }}</a>
                            <small class="table-secondary">{{ $material->stock_unit }}</small>
                        </td>
                        <td data-label="CAS No.">
                            @if($isProd)
                                @if($material->recipes->isNotEmpty())
                                    <span style="font-size: 11.5px; font-weight: 600; color: #087f70;">{{ $material->recipes->count() }} {{ \Illuminate\Support\Str::plural('ingredient', $material->recipes->count()) }}</span>
                                @else
                                    <span style="color: #94a3b8; font-size: 12px;">No recipe</span>
                                @endif
                            @else
                                {{ $material->cas_number ?: '—' }}
                            @endif
                        </td>
                        <td data-label="Category">
                            <span class="materials-category">
                                {{ $isProd ? ($material->productCategory?->name ?? 'Uncategorized') : ($material->category?->name ?? 'Uncategorized') }}
                            </span>
                        </td>
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
                    <tr>
                        <td colspan="{{ $currentType === 'all' ? 10 : 9 }}">
                            <div class="empty-state">
                                <span class="empty-mark">▤</span>
                                <strong>No {{ $currentType === 'product' ? 'products' : 'materials' }} found</strong>
                                <p>Add a {{ $currentType === 'product' ? 'product' : 'material' }} or adjust your search filters.</p>
                                @if(auth()->user()->hasPermission('materials.create'))
                                    <a class="button button-primary" href="{{ route('materials.create', ['type' => $currentType === 'product' ? 'product' : 'material']) }}">
                                        Add your first {{ $currentType === 'product' ? 'product' : 'material' }}
                                    </a>
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
            <div class="pagination-wrap">{{ $materials->onEachSide(2)->links() }}</div>
        </div>
    </section>
@endsection
