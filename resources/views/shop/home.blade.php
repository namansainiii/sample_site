@extends('layouts.app')

@section('flush')
<section class="hero">
    <div class="container">
        <div>
            <span class="eyebrow">Wholesale fasteners · Retail convenience</span>
            <h1>Every bolt, nut &amp; screw.<br><span>One reliable supplier.</span></h1>
            <p class="lead">Stainless, high-tensile and zinc-plated fasteners in every common size, sold by the pack at trade prices. Order online with GST invoice, or send us your BOQ for a bulk quote.</p>
            <div class="hero-actions">
                <a href="{{ route('shop') }}" class="btn btn-primary btn-lg">Shop the catalogue</a>
                <a href="{{ route('quotes.create') }}" class="btn btn-outline btn-lg">Get a bulk quote</a>
            </div>
            <div class="hero-stats">
                <div>{{ $categories->sum('products_count') }}+<small>product lines</small></div>
                <div>{{ \App\Models\ProductVariant::count() }}+<small>sizes in stock</small></div>
                <div>24h<small>dispatch on stock items</small></div>
            </div>
        </div>
        <div class="hero-art" aria-hidden="true">
            @foreach($categories->take(6) as $tile)
                <a class="tile" href="{{ route('shop.category', $tile) }}" tabindex="-1"><img src="{{ $tile->iconUrl() }}" alt=""></a>
            @endforeach
        </div>
    </div>
</section>

<section class="trust">
    <div class="container">
        <div class="trust-item">
            <span class="ico"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M7 3h10l3 4v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7z"/></svg></span>
            <div><strong>GST tax invoice</strong><span>Claim input credit on every order</span></div>
        </div>
        <div class="trust-item">
            <span class="ico"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 7h13v10H3zM16 10h4l1 3v4h-5M7 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm11 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/></svg></span>
            <div><strong>Pan-India delivery</strong><span>Free above @inr(config('store.free_shipping_threshold'))</span></div>
        </div>
        <div class="trust-item">
            <span class="ico"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3v18m-6-6 6 6 6-6"/></svg></span>
            <div><strong>Bulk discounts</strong><span>Auto price drop at 10 &amp; 50 packs</span></div>
        </div>
        <div class="trust-item">
            <span class="ico"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6z"/></svg></span>
            <div><strong>Secure payment</strong><span>UPI, cards, netbanking or COD</span></div>
        </div>
    </div>
</section>

<div class="container">
    @include('partials.flash')

    <section class="section">
        <div class="section-head">
            <div>
                <span class="eyebrow">Browse by type</span>
                <h2>Shop by category</h2>
            </div>
            <a href="{{ route('shop') }}" class="btn btn-outline btn-sm">View all products →</a>
        </div>
        <div class="cat-grid">
            @foreach($categories as $category)
                <a href="{{ route('shop.category', $category) }}" class="cat-tile">
                    <img src="{{ $category->iconUrl() }}" alt="">
                    <div>
                        <strong>{{ $category->name }}</strong>
                        <span>{{ $category->products_count }} {{ Str::plural('product', $category->products_count) }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    @if($featured->isNotEmpty())
        <section class="section" style="padding-top:0;">
            <div class="section-head">
                <div>
                    <span class="eyebrow">Fast movers</span>
                    <h2>Best sellers</h2>
                </div>
                <p>Most-ordered lines by fabricators and contractors this month.</p>
            </div>
            <div class="product-grid">
                @foreach($featured as $product)
                    @include('partials.product-card')
                @endforeach
            </div>
        </section>
    @endif

    <section class="section" style="padding-top:0;">
        <div class="grid grid-2">
            <div class="callout">
                <div>
                    <span class="eyebrow">For workshops &amp; contractors</span>
                    <h2>Know your SKUs? Order in seconds.</h2>
                    <p>Paste a list like <code>HB304-M8X40, 4</code> and we fill your cart instantly.</p>
                </div>
                <a href="{{ route('cart.index') }}#quick-order" class="btn btn-primary">Quick order</a>
            </div>
            <div class="callout" style="background:linear-gradient(135deg,#92400e,#d97706);">
                <div>
                    <span class="eyebrow" style="color:#fde68a;">Projects &amp; OEM</span>
                    <h2>Buying 10,000+ pieces?</h2>
                    <p>Send your requirement or BOQ and get a custom price within 24 hours.</p>
                </div>
                <a href="{{ route('quotes.create') }}" class="btn btn-dark">Request quote</a>
            </div>
        </div>
    </section>
</div>
@endsection
