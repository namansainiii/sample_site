@extends('layouts.app')

@section('title', $product->name)
@section('description', Str::limit($product->description, 155))

@php
    $variants = $product->variants;
    $first = $variants->firstWhere('stock', '>', 0) ?? $variants->first();
    $variantData = $variants->mapWithKeys(fn ($v) => [$v->id => [
        'price' => (float) $v->price,
        'stock' => $v->stock,
        'pack' => $v->pack_qty,
        'min' => $v->min_order_qty,
        'tiers' => $v->priceTiers->map(fn ($t) => ['min' => $t->min_qty, 'price' => (float) $t->unit_price])->values(),
    ]]);
@endphp

@push('head')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $product->name,
    'description' => $product->description,
    'brand' => ['@type' => 'Brand', 'name' => $product->brand],
    'image' => $product->imageUrl(),
    'offers' => [
        '@type' => 'AggregateOffer',
        'priceCurrency' => 'INR',
        'lowPrice' => $variants->min('price'),
        'highPrice' => $variants->max('price'),
        'availability' => $product->totalStock() > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
</script>
@endpush

@section('content')
<div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Home</a>
        <span><a href="{{ route('shop.category', $product->category) }}">{{ $product->category->name }}</a></span>
        <span>{{ $product->name }}</span>
    </nav>

    <div class="pdp">
        <div>
            <div class="pdp-media">
                <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" @class(['photo' => $product->image_path])>
            </div>
        </div>

        <div>
            <div class="meta muted" style="font-weight:600; text-transform:uppercase; font-size:.78rem; letter-spacing:.6px;">{{ $product->brand }} · {{ $product->category->name }}</div>
            <h1>{{ $product->name }}</h1>
            <div class="chips">
                @if($product->standard)<span class="badge badge-info">{{ $product->standard }}</span>@endif
                @if($product->material)<span class="badge">{{ $product->material }}</span>@endif
                @if($product->finish)<span class="badge">{{ $product->finish }}</span>@endif
                @if($product->hsn_code)<span class="badge">HSN {{ $product->hsn_code }}</span>@endif
            </div>
            <p>{{ $product->description }}</p>

            @if($variants->isEmpty())
                <div class="alert alert-warning">This product is currently unavailable.</div>
            @else
                <form method="POST" action="{{ route('cart.add') }}" data-ajax-cart id="buyForm">
                    @csrf
                    <div class="card" style="overflow:hidden;">
                        <div class="table-wrap">
                            <table class="variant-table">
                                <thead>
                                    <tr><th>Size</th><th>Pack</th><th class="num">Price</th><th class="num">Per pc</th><th>Stock</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($variants as $variant)
                                        <tr @class(['selected' => $variant->is($first)]) data-variant-row="{{ $variant->id }}">
                                            <td>
                                                <label>
                                                    <input type="radio" name="variant_id" value="{{ $variant->id }}" @checked($variant->is($first)) @disabled($variant->stock < 1)>
                                                    {{ $variant->size }}
                                                </label>
                                                <small class="mono">{{ $variant->sku }}</small>
                                            </td>
                                            <td>{{ $variant->pack_qty > 1 ? $variant->pack_qty.' pcs' : 'Single' }}</td>
                                            <td class="num">
                                                <strong>@inr($variant->price)</strong>
                                                @if($variant->mrp && $variant->mrp > $variant->price)<br><span class="strike">@inr($variant->mrp)</span>@endif
                                            </td>
                                            <td class="num">@inr($variant->pricePerPiece())</td>
                                            <td>
                                                @if($variant->stock < 1)
                                                    <span class="stock-out">Out of stock</span>
                                                @elseif($variant->isLowStock())
                                                    <span class="stock-low">Only {{ $variant->stock }} left</span>
                                                @else
                                                    <span class="stock-ok">In stock</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="buybox">
                        <div class="row between">
                            <div>
                                <div class="muted" style="font-size:.82rem;">Total (excl. GST) for <span id="selQty">1</span> pack(s)</div>
                                <div class="total" id="selTotal">@inr($first->price)</div>
                                <div class="muted" style="font-size:.8rem;">+ {{ rtrim(rtrim($product->gst_rate, '0'), '.') }}% GST · <span id="selPieces">{{ $first->pack_qty }}</span> pieces</div>
                            </div>
                            <div class="row">
                                <div class="qty">
                                    <button type="button" data-step="-1" aria-label="Decrease">−</button>
                                    <input type="number" name="quantity" id="qtyInput" value="{{ $first->min_order_qty }}" min="1" max="10000" aria-label="Quantity in packs">
                                    <button type="button" data-step="1" aria-label="Increase">+</button>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg" @disabled($product->totalStock() < 1)>Add to cart</button>
                            </div>
                        </div>
                        <div class="tier-note" id="tierNote" style="margin-top:12px;" hidden></div>
                    </div>
                </form>
            @endif

            <div class="row" style="margin-top:14px;">
                <a class="btn btn-outline" href="{{ route('quotes.create', ['product' => $product->slug]) }}">Need 10,000+ pcs? Get a quote</a>
                <a class="btn btn-whatsapp" target="_blank" rel="noopener" href="https://wa.me/{{ config('store.whatsapp') }}?text={{ rawurlencode('Hi, I want to enquire about '.$product->name.' — '.route('product.show', $product)) }}">Ask on WhatsApp</a>
            </div>

            @if($product->specs)
                <h3 style="margin-top:28px;">Specifications</h3>
                <div class="card" style="overflow:hidden;">
                    <table class="spec-table">
                        @foreach($product->specs as $key => $value)
                            <tr><th>{{ $key }}</th><td>{{ $value }}</td></tr>
                        @endforeach
                        @if($product->standard)<tr><th>Standard</th><td>{{ $product->standard }}</td></tr>@endif
                        @if($product->material)<tr><th>Material</th><td>{{ $product->material }}</td></tr>@endif
                        @if($product->finish)<tr><th>Finish</th><td>{{ $product->finish }}</td></tr>@endif
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if($related->isNotEmpty())
        <section class="section" style="padding-top:0;">
            <div class="section-head"><h2>You may also need</h2></div>
            <div class="product-grid">
                @foreach($related as $relatedProduct)
                    @include('partials.product-card', ['product' => $relatedProduct])
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    const variants = @json($variantData);
    const form = document.getElementById('buyForm');
    if (!form) return;
    const qtyInput = document.getElementById('qtyInput');
    const fmt = (n) => '₹' + n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function selected() {
        const radio = form.querySelector('input[name=variant_id]:checked');
        return radio ? variants[radio.value] : null;
    }

    function refresh() {
        const v = selected();
        if (!v) return;
        const qty = Math.max(1, parseInt(qtyInput.value, 10) || 1);
        let unit = v.price;
        v.tiers.forEach((t) => { if (qty >= t.min) unit = Math.min(unit, t.price); });
        document.getElementById('selQty').textContent = qty;
        document.getElementById('selTotal').textContent = fmt(unit * qty);
        document.getElementById('selPieces').textContent = (qty * v.pack).toLocaleString('en-IN');
        qtyInput.max = Math.max(1, v.stock);

        const note = document.getElementById('tierNote');
        const next = v.tiers.find((t) => qty < t.min);
        if (unit < v.price) {
            note.hidden = false;
            note.textContent = `Bulk price applied: ${fmt(unit)} per pack (save ${fmt((v.price - unit) * qty)}).` + (next ? ` Buy ${next.min}+ for ${fmt(next.price)}/pack.` : '');
        } else if (next) {
            note.hidden = false;
            note.textContent = `Buy ${next.min}+ packs and pay only ${fmt(next.price)} per pack.`;
        } else {
            note.hidden = true;
        }
        form.querySelectorAll('[data-variant-row]').forEach((row) => {
            row.classList.toggle('selected', row.querySelector('input').checked);
        });
    }

    form.addEventListener('change', refresh);
    qtyInput.addEventListener('input', refresh);
    refresh();
})();
</script>
@endpush
