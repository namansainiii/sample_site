@php
    $from = $product->startingPrice();
    $stock = $product->totalStock();
    $cheapest = $product->variants->sortBy('price')->first();
@endphp
<article class="product-card">
    <a href="{{ route('product.show', $product) }}" class="media">
        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" loading="lazy" @class(['photo' => $product->image_path])>
        @if($product->is_featured)
            <span class="badge badge-accent">Best seller</span>
        @endif
    </a>
    <div class="body">
        <div class="meta">{{ $product->category->name }}@if($product->standard) · {{ $product->standard }}@endif</div>
        <h3><a href="{{ route('product.show', $product) }}">{{ $product->name }}</a></h3>
        <div class="chips">
            @if($product->material)<span class="badge">{{ $product->material }}</span>@endif
            <span class="badge">{{ $product->variants->count() }} {{ Str::plural('size', $product->variants->count()) }}</span>
        </div>
        <div class="price-row">
            <div>
                @if($from !== null)
                    <div class="price"><small>from</small> @inr($from)</div>
                    @if($cheapest && $cheapest->pack_qty > 1)
                        <small>{{ $cheapest->pack_qty }} pcs · @inr($cheapest->pricePerPiece())/pc</small>
                    @endif
                @endif
                <div class="{{ $stock > 0 ? 'stock-ok' : 'stock-out' }}">{{ $stock > 0 ? 'In stock' : 'Out of stock' }}</div>
            </div>
            <a href="{{ route('product.show', $product) }}" class="btn btn-dark btn-sm">Select size</a>
        </div>
    </div>
</article>
