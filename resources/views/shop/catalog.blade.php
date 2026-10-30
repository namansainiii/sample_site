@extends('layouts.app')

@section('title', $category?->name ?? (filled($filters['q'] ?? null) ? 'Search: '.$filters['q'] : 'All Products'))
@section('description', $category?->description ?? 'Browse bolts, nuts, screws, washers, anchors and tools by size, material and standard.')

@section('content')
@php($baseRoute = $category ? route('shop.category', $category) : route('shop'))
<div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Home</a>
        <span><a href="{{ route('shop') }}">Shop</a></span>
        @if($category)<span>{{ $category->name }}</span>@endif
    </nav>

    <div class="section-head" style="margin-bottom:14px;">
        <div>
            <h1 style="margin:0;">{{ $category?->name ?? 'All Products' }}</h1>
            @if($category?->description)<p>{{ $category->description }}</p>@endif
        </div>
    </div>

    <div class="catalog">
        <aside class="filters card card-body">
            <form method="GET" action="{{ $baseRoute }}">
                <h4>Search</h4>
                <input class="input" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Size, SKU, standard…">

                <h4>Category</h4>
                <ul class="filter-list">
                    <li><a href="{{ route('shop', request()->except('page')) }}" @class(['active' => ! $category])>All categories</a></li>
                    @foreach($categories as $item)
                        <li><a href="{{ route('shop.category', [$item, ...request()->except('page', 'material')]) }}" @class(['active' => $category?->is($item)])>{{ $item->name }}</a></li>
                    @endforeach
                </ul>

                @if($materials->isNotEmpty())
                    <h4>Material</h4>
                    <select class="input" name="material" onchange="this.form.submit()">
                        <option value="">Any material</option>
                        @foreach($materials as $material)
                            <option value="{{ $material }}" @selected(($filters['material'] ?? '') === $material)>{{ $material }}</option>
                        @endforeach
                    </select>
                @endif

                <h4>Availability</h4>
                <label class="check"><input type="checkbox" name="in_stock" value="1" @checked(request()->boolean('in_stock')) onchange="this.form.submit()"> In stock only</label>

                <input type="hidden" name="sort" value="{{ $filters['sort'] ?? 'popular' }}">
                <button class="btn btn-dark btn-block" style="margin-top:18px;" type="submit">Apply filters</button>
                @if(array_filter($filters))
                    <a href="{{ $baseRoute }}" class="btn btn-ghost btn-block" style="margin-top:6px;">Clear all</a>
                @endif
            </form>
        </aside>

        <section>
            <div class="toolbar">
                <span class="muted">{{ $products->total() }} {{ Str::plural('product', $products->total()) }}@if(filled($filters['q'] ?? null)) for “{{ $filters['q'] }}”@endif</span>
                <form method="GET" action="{{ $baseRoute }}" class="row">
                    @foreach(request()->except('sort', 'page') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <label for="sort" class="muted" style="font-weight:500;">Sort</label>
                    <select id="sort" class="input" name="sort" onchange="this.form.submit()">
                        <option value="popular" @selected(($filters['sort'] ?? 'popular') === 'popular')>Best sellers</option>
                        <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>Price: low to high</option>
                        <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>Price: high to low</option>
                        <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>Newest</option>
                    </select>
                </form>
            </div>

            @if($products->isEmpty())
                <div class="card empty">
                    <h3>No products match your filters</h3>
                    <p>Can't find the exact size or grade? We source specials on request.</p>
                    <a class="btn btn-primary" href="{{ route('quotes.create') }}">Ask for it</a>
                </div>
            @else
                <div class="product-grid" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr));">
                    @foreach($products as $product)
                        @include('partials.product-card')
                    @endforeach
                </div>
                {{ $products->links() }}
            @endif
        </section>
    </div>
</div>
@endsection
