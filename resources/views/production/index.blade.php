@extends('layouts.app')
@section('title', 'Production')
@section('subtitle', 'Manage production records for your manufactured products.')
@section('actions')
    @if(auth()->user()->hasPermission('production.create'))
        <a class="button button-primary production-create-action" href="{{ route('production.create') }}" id="production-create-toggle">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Create new Production</span>
        </a>
    @endif
@endsection

@section('content')
<div class="production-workspace {{ $createOpen ? 'production-workspace-open' : '' }}" id="production-workspace">
    <section class="production-catalog" aria-label="Production records">
        <form class="production-filters" method="GET" action="{{ route('production.index') }}">
            <div class="production-search">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search product name, code or category…" aria-label="Search production records">
            </div>
            <div class="production-category-filter">
                <span class="production-filter-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                </span>
                <select name="category_id" aria-label="Filter by category">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <svg class="production-filter-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </div>
            <button class="button button-secondary production-filter-button" type="submit">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16l-6.5 7.5V19l-3 1v-7.5L4 5Z"/></svg>
                <span>Filter</span>
            </button>
        </form>

        @if($productionGroups->isNotEmpty())
            <!-- Prompt shown when no category is selected in the form -->
            <div class="production-category-prompt production-prompt-hidden" id="production-category-prompt">
                <span class="empty-mark"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg></span>
                <strong>Select a Product Category</strong>
                <p>Choose or create a Product Category in the form to see and manage pre-defined production cards under it.</p>
            </div>

            <div class="production-category-groups" id="production-category-groups">
                @php
                    $colorMap = [
                        'HS-001' => 'red',
                        'DC-002' => 'green',
                        'SW-003' => 'blue',
                        'GC-004' => 'orange',
                        'BL-001' => 'green',
                        'FC-002' => 'orange',
                        'TC-003' => 'red',
                        'MPC-004' => 'purple',
                        'AF-005' => 'orange',
                        'DT-001' => 'orange',
                        'FS-002' => 'purple',
                    ];
                    $palette = ['red', 'green', 'blue', 'orange', 'purple'];
                @endphp

                <!-- Live dynamic preview group when typing a new category in the form -->
                <details class="production-category-group production-category-hidden" id="production-dynamic-group" open>
                    <summary class="production-category-heading">
                        <span class="production-category-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4a2 2 0 0 0 1.8-3l-5-9V3"/>
                                <path d="M8 15h8"/>
                            </svg>
                        </span>
                        <span class="production-category-copy">
                            <strong id="dynamic-group-title">New Category</strong>
                            <small id="dynamic-group-count">1 product card preview</small>
                        </span>
                        <span class="production-new-tag">New Category</span>
                    </summary>
                    <div class="production-product-grid" id="dynamic-product-grid">
                        <article class="production-product-card production-card-preview" id="dynamic-preview-card">
                            <div class="production-card-top">
                                <span class="production-product-icon production-icon-blue">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4a2 2 0 0 0 1.8-3l-5-9V3"/>
                                        <path d="M8 15h8"/>
                                    </svg>
                                </span>
                                <div class="production-product-copy">
                                    <strong id="dynamic-card-name">New Production</strong>
                                    <small id="dynamic-card-code">Auto Code</small>
                                </div>
                                <button type="button" class="production-product-menu" aria-label="Product options">⋮</button>
                            </div>
                            <div class="production-product-meta">
                                <span class="production-meta-row" title="Production Date">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                        <line x1="16" y1="2" x2="16" y2="6"/>
                                        <line x1="8" y1="2" x2="8" y2="6"/>
                                        <line x1="3" y1="10" x2="21" y2="10"/>
                                    </svg>
                                    <span id="dynamic-card-date">{{ now()->format('Y-m-d') }}</span>
                                </span>
                                <span class="production-meta-row" title="Quantity">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M12 3 21 8v8l-9 5-9-5V8l9-5Z"/>
                                        <path d="m3 8 9 5 9-5M12 13v8"/>
                                    </svg>
                                    <span id="dynamic-card-qty">—</span>
                                </span>
                            </div>
                        </article>
                    </div>
                </details>

                @foreach($productionGroups as $group)
                    <details class="production-category-group" data-category-id="{{ $group['category_id'] }}" open>
                        <summary class="production-category-heading">
                            <span class="production-category-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4a2 2 0 0 0 1.8-3l-5-9V3"/>
                                    <path d="M8 15h8"/>
                                </svg>
                            </span>
                            <span class="production-category-copy">
                                <strong>{{ $group['name'] }}</strong>
                                <small>{{ $group['count'] }} {{ \Illuminate\Support\Str::plural('product', $group['count']) }}</small>
                            </span>
                            <svg class="production-category-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m7 10 5 5 5-5"/></svg>
                        </summary>
                        <div class="production-product-grid">
                            <!-- Dynamic preview card when typing a new production name under this category -->
                            <article class="production-product-card production-card-preview" data-category-preview-card style="display: none;">
                                <div class="production-card-top">
                                    <span class="production-product-icon production-icon-purple">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4a2 2 0 0 0 1.8-3l-5-9V3"/>
                                            <path d="M8 15h8"/>
                                        </svg>
                                    </span>
                                    <div class="production-product-copy">
                                        <strong data-category-preview-name>New Production</strong>
                                        <small>Pending Save</small>
                                    </div>
                                    <button type="button" class="production-product-menu" aria-label="Product options">⋮</button>
                                </div>
                                <div class="production-product-meta">
                                    <span class="production-meta-row" title="Production Date">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        <span>{{ now()->format('Y-m-d') }}</span>
                                    </span>
                                    <span class="production-meta-row" title="Quantity">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3 21 8v8l-9 5-9-5V8l9-5Z"/><path d="m3 8 9 5 9-5M12 13v8"/></svg>
                                        <span data-category-preview-qty>—</span>
                                    </span>
                                </div>
                            </article>

                            @forelse($group['products'] as $product)
                                @php
                                    $latestOutput = $product->stockMovements->first();
                                    $lot = $latestOutput?->materialLot ?? $product->lots->first();
                                    $code = $product->item_code;
                                    $cardColor = $colorMap[$code] ?? $palette[$loop->index % count($palette)];
                                    $prodDate = $latestOutput?->created_at?->format('Y-m-d') ?? $product->created_at?->format('Y-m-d');
                                    $prodQty = $latestOutput?->quantity ?? $product->usable_stock ?? 0;

                                    $productIngredients = $product->recipes->map(function ($r) {
                                        return [
                                            'material_id' => $r->material_id,
                                            'quantity' => (float) $r->quantity,
                                            'unit' => $r->material?->stock_unit ?? 'kg',
                                        ];
                                    });
                                    if ($productIngredients->isEmpty() && $latestOutput?->reference_id) {
                                        $relOrder = \App\Models\ProductionOrder::find($latestOutput->reference_id);
                                        if ($relOrder) {
                                            $productIngredients = $relOrder->items()
                                                ->where('direction', 'input')
                                                ->with('material')
                                                ->get()
                                                ->map(fn($it) => [
                                                    'material_id' => $it->material_id,
                                                    'quantity' => (float) $it->quantity,
                                                    'unit' => $it->material?->stock_unit ?? 'kg',
                                                ]);
                                        }
                                    }
                                @endphp
                                <article class="production-product-card" 
                                    data-product-id="{{ $product->id }}" 
                                    data-category-id="{{ $group['category_id'] }}"
                                    data-product-name="{{ $product->name }}"
                                    data-product-code="{{ $product->item_code }}"
                                    data-quantity="{{ rtrim(rtrim(number_format((float) $prodQty, 3), '0'), '.') }}"
                                    data-unit="{{ $product->stock_unit }}"
                                    data-batch-number="{{ $lot?->lot_number ?? '' }}"
                                    data-date="{{ $prodDate }}"
                                    data-expiry="{{ $lot?->expiry_date?->format('Y-m-d') ?? '' }}"
                                    data-notes="{{ $latestOutput?->notes ?? $lot?->notes ?? '' }}"
                                    data-ingredients="{{ json_encode($productIngredients) }}"
                                    title="Click to view and edit production details">
                                    <div class="production-card-top">
                                        <span class="production-product-icon production-icon-{{ $cardColor }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4a2 2 0 0 0 1.8-3l-5-9V3"/>
                                                <path d="M8 15h8"/>
                                            </svg>
                                        </span>
                                        <div class="production-product-copy">
                                            <strong title="{{ $product->name }}">{{ $product->name }}</strong>
                                            <small>{{ $product->item_code }}</small>
                                        </div>
                                        <button type="button" class="production-product-menu" aria-label="Product options">⋮</button>
                                    </div>
                                    <div class="production-product-meta">
                                        <span class="production-meta-row" title="Production Date">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                                <line x1="16" y1="2" x2="16" y2="6"/>
                                                <line x1="8" y1="2" x2="8" y2="6"/>
                                                <line x1="3" y1="10" x2="21" y2="10"/>
                                            </svg>
                                            <span>{{ $prodDate }}</span>
                                        </span>
                                        <span class="production-meta-row" title="Quantity">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M12 3 21 8v8l-9 5-9-5V8l9-5Z"/>
                                                <path d="m3 8 9 5 9-5M12 13v8"/>
                                            </svg>
                                            <span>{{ rtrim(rtrim(number_format((float) $prodQty, 2), '0'), '.') }} {{ $product->stock_unit }}</span>
                                        </span>
                                        @if($lot)
                                            <span class="sr-only">
                                                <span>{{ $lot->lot_number ?? $lot->batch_number }}</span>
                                                @if($lot->expiry_date)
                                                    <span>Expires {{ $lot->expiry_date->toDateString() }}</span>
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                </article>
                            @empty
                                <div class="production-empty-category-card">
                                    <p>No product cards yet in <strong>{{ $group['name'] }}</strong>.</p>
                                    <small>Select this category and type a Production Name in the form to add a card.</small>
                                </div>
                            @endforelse
                        </div>
                    </details>
                @endforeach
            </div>
            <div class="production-pagination">
                <span>Showing {{ $productionGroups->count() }} {{ \Illuminate\Support\Str::plural('category section', $productionGroups->count()) }}</span>
            </div>
        @else
            <div class="production-empty-state">
                <span class="empty-mark"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4a2 2 0 0 0 1.8-3l-5-9V3"/></svg></span>
                <strong>{{ request()->filled('q') || request()->filled('category_id') ? 'No matching production records' : 'No production records yet' }}</strong>
                <p>{{ request()->filled('q') || request()->filled('category_id') ? 'Try changing your search or category filter.' : 'Record a production run to see finished products grouped by category.' }}</p>
                @if(auth()->user()->hasPermission('production.create'))
                    <a class="button button-primary" href="{{ route('production.create') }}">Create new Production</a>
                @endif
            </div>
        @endif
    </section>

    @if(auth()->user()->hasPermission('production.create'))
        <aside class="production-create-panel" id="production-create-panel" aria-labelledby="production-create-title">
            <header class="production-create-panel-header">
                <div>
                    <span class="production-edit-mode-badge" id="production-edit-mode-badge" style="display: none;">EDITING MODE</span>
                    <h2 id="production-create-title">Create new Production</h2>
                    <p id="production-create-subtitle">Record a new production entry for a product.</p>
                </div>
                <button type="button" class="production-panel-close" id="production-panel-close-btn" aria-label="Close production form">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </header>
            @include('production.create-panel')
        </aside>
    @endif
</div>
@endsection
