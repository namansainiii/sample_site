@extends('layouts.app')

@section('title', 'Your Cart')

@section('content')
<div class="container">
    <h1>Your cart</h1>

    <div class="layout-2">
        <div class="stack">
            @if($lines->isEmpty())
                <div class="card empty">
                    <h3>Your cart is empty</h3>
                    <p>Browse the catalogue or paste a list of SKUs below.</p>
                    <a href="{{ route('shop') }}" class="btn btn-primary">Start shopping</a>
                </div>
            @else
                <div class="card" style="overflow:hidden;">
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr><th>Product</th><th class="num">Price / pack</th><th>Packs</th><th class="num">Amount</th><th></th></tr>
                            </thead>
                            <tbody>
                                @foreach($lines as $line)
                                    @php($variant = $line['variant'])
                                    <tr>
                                        <td class="item-cell">
                                            <div class="row" style="flex-wrap:nowrap;">
                                                <img class="thumb" src="{{ $variant->product->imageUrl() }}" alt="">
                                                <div>
                                                    <a href="{{ route('product.show', $variant->product) }}"><strong>{{ $variant->product->name }}</strong></a><br>
                                                    <small>{{ $variant->label() }} · <span class="mono">{{ $variant->sku }}</span></small>
                                                    @if($variant->isLowStock())<br><span class="stock-low">Only {{ $variant->stock }} left</span>@endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="num">
                                            @inr($line['unit_price'])
                                            @if($line['unit_price'] < (float) $variant->price)
                                                <br><span class="badge badge-ok">Bulk price</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('cart.update', $variant) }}" data-autosubmit>
                                                @csrf @method('PATCH')
                                                <div class="qty">
                                                    <button type="button" data-step="-1" aria-label="Decrease">−</button>
                                                    <input type="number" name="quantity" value="{{ $line['quantity'] }}" min="{{ $variant->min_order_qty }}" max="{{ $variant->stock }}" aria-label="Packs">
                                                    <button type="button" data-step="1" aria-label="Increase">+</button>
                                                </div>
                                                <noscript><button class="btn btn-sm btn-outline" type="submit">Update</button></noscript>
                                            </form>
                                        </td>
                                        <td class="num"><strong>@inr($line['line_total'])</strong></td>
                                        <td>
                                            <form method="POST" action="{{ route('cart.remove', $variant) }}">
                                                @csrf @method('DELETE')
                                                <button class="link-btn" type="submit" aria-label="Remove">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="card" id="quick-order">
                <div class="card-head">
                    <h3>⚡ Quick order by SKU</h3>
                    <small>One item per line: <code>SKU, packs</code></small>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('cart.quick-order') }}">
                        @csrf
                        <textarea class="input mono" name="lines" rows="5" placeholder="HB304-M8X40, 4&#10;HN304-M8, 2&#10;SW-M8, 2">{{ old('lines') }}</textarea>
                        <div class="row between" style="margin-top:12px;">
                            <small>SKUs are shown on every product page. Paste straight from Excel too.</small>
                            <button type="submit" class="btn btn-dark">Add all to cart</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <aside class="summary card card-body">
            <h3>Order summary</h3>
            <div class="summary-row"><span>Subtotal</span><span>@inr($totals['subtotal'])</span></div>
            <div class="summary-row"><span>GST</span><span>@inr($totals['gst'])</span></div>
            <div class="summary-row"><span>Shipping</span><span>{{ $totals['shipping'] > 0 ? \App\Support\Money::inr($totals['shipping']) : 'FREE' }}</span></div>
            <div class="summary-row total"><span>Total</span><span>@inr($totals['total'])</span></div>

            @if($totals['subtotal'] > 0 && $totals['free_shipping_gap'] > 0)
                @php($threshold = (float) config('store.free_shipping_threshold'))
                <div class="progress"><span style="width: {{ min(100, round($totals['subtotal'] / $threshold * 100)) }}%"></span></div>
                <small>Add <strong>@inr($totals['free_shipping_gap'])</strong> more for free shipping.</small>
            @endif

            <a href="{{ route('checkout') }}" @class(['btn btn-primary btn-lg btn-block', 'disabled' => $lines->isEmpty()]) style="margin-top:16px;" @if($lines->isEmpty()) aria-disabled="true" onclick="return false" @endif>Proceed to checkout</a>
            <a href="{{ route('shop') }}" class="btn btn-ghost btn-block" style="margin-top:6px;">Continue shopping</a>
            <hr>
            <small>Need a better price for large quantities? <a href="{{ route('quotes.create') }}" style="text-decoration:underline;">Request a bulk quote</a>.</small>
        </aside>
    </div>
</div>
@endsection
