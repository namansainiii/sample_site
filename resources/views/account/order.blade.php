@extends('layouts.app')

@section('title', 'Order '.$order->order_number)

@section('content')
@php
    $steps = \App\Enums\OrderStatus::timeline();
    $currentIndex = array_search($order->status, $steps, true);
@endphp
<div class="container" style="padding-bottom:40px;">
    <nav class="breadcrumb"><a href="{{ route('account.orders') }}">My orders</a><span>{{ $order->order_number }}</span></nav>

    <div class="section-head">
        <div>
            <h1 style="margin:0;">Order <span class="mono" style="font-size:.8em;">{{ $order->order_number }}</span></h1>
            <p>Placed {{ $order->created_at->format('d M Y, h:i A') }} · @include('partials.order-status')</p>
        </div>
        <div class="row">
            @if($order->awaitingOnlinePayment())
                <a href="{{ route('checkout.pay', $order) }}" class="btn btn-primary">Pay @inr($order->total) now</a>
            @endif
            <a href="{{ route('account.orders.invoice', $order) }}" target="_blank" class="btn btn-outline">Download invoice</a>
        </div>
    </div>

    <div class="layout-2">
        <div class="stack">
            <div class="card card-body">
                @if($order->status === \App\Enums\OrderStatus::Cancelled)
                    <div class="alert alert-error" style="margin:0;">This order was cancelled. If you paid online, the refund is processed to the original payment method within 5–7 working days.</div>
                @else
                    <div class="timeline">
                        <div class="timeline-bar" style="width: calc({{ $currentIndex === false ? 0 : $currentIndex / (count($steps) - 1) * 100 }}% - 28px);"></div>
                        @foreach($steps as $index => $step)
                            <div @class(['step', 'done' => $currentIndex !== false && $index <= $currentIndex])>
                                <span class="dot">{{ $currentIndex !== false && $index <= $currentIndex ? '✓' : $index + 1 }}</span>
                                <span class="lbl">{{ $step->label() }}</span>
                            </div>
                        @endforeach
                    </div>
                    @if($order->tracking_number)
                        <p style="margin:14px 0 0;">Courier tracking no: <strong class="mono">{{ $order->tracking_number }}</strong></p>
                    @endif
                @endif
            </div>

            <div class="card" style="overflow:hidden;">
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Item</th><th class="num">Rate</th><th class="num">Packs</th><th class="num">Amount</th></tr></thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        @if($item->variant?->product)
                                            <a href="{{ route('product.show', $item->variant->product) }}"><strong>{{ $item->product_name }}</strong></a>
                                        @else
                                            <strong>{{ $item->product_name }}</strong>
                                        @endif
                                        <br><small>{{ $item->variant_label }} · <span class="mono">{{ $item->sku }}</span></small>
                                    </td>
                                    <td class="num">@inr($item->unit_price)</td>
                                    <td class="num">{{ $item->quantity }}</td>
                                    <td class="num">@inr($item->line_total)</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <aside class="stack">
            <div class="card card-body">
                <h3>Payment</h3>
                <div class="summary-row"><span>Subtotal</span><span>@inr($order->subtotal)</span></div>
                <div class="summary-row"><span>GST</span><span>@inr($order->gst_amount)</span></div>
                <div class="summary-row"><span>Shipping</span><span>{{ (float) $order->shipping_fee > 0 ? \App\Support\Money::inr($order->shipping_fee) : 'FREE' }}</span></div>
                <div class="summary-row total"><span>Total</span><span>@inr($order->total)</span></div>
                <small>{{ $order->payment_method->label() }}</small>
            </div>
            <div class="card card-body">
                <h3>Delivering to</h3>
                <p style="margin:0;">
                    <strong>{{ $order->shipping_name }}</strong><br>
                    {{ $order->shipping_address }}<br>
                    {{ $order->shipping_city }}, {{ $order->shipping_state }} – {{ $order->shipping_pincode }}<br>
                    📞 {{ $order->shipping_phone }}
                </p>
                @if($order->gstin)
                    <hr style="margin:12px 0;">
                    <small>{{ $order->company_name }}<br>GSTIN <span class="mono">{{ $order->gstin }}</span></small>
                @endif
            </div>
            <a class="btn btn-whatsapp btn-block" target="_blank" rel="noopener" href="https://wa.me/{{ config('store.whatsapp') }}?text={{ rawurlencode('Hi, I need help with order '.$order->order_number) }}">Need help? WhatsApp us</a>
        </aside>
    </div>
</div>
@endsection
