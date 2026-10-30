@extends('layouts.admin')

@section('title', 'Order '.$order->order_number)

@section('content')
<div class="admin-top">
    <div>
        <a href="{{ route('admin.orders.index') }}" class="muted">← Orders</a>
        <h1>Order <span class="mono" style="font-size:.8em;">{{ $order->order_number }}</span></h1>
        <small>{{ $order->created_at->format('d M Y, h:i A') }} · @include('partials.order-status')</small>
    </div>
    <div class="row">
        <a class="btn btn-outline" target="_blank" href="{{ route('account.orders.invoice', $order) }}">🧾 Invoice</a>
        <a class="btn btn-whatsapp" target="_blank" rel="noopener" href="https://wa.me/91{{ $order->shipping_phone }}?text={{ rawurlencode('Hi '.$order->shipping_name.', update on your '.config('store.name').' order '.$order->order_number.': '.$order->status->label()) }}">WhatsApp customer</a>
    </div>
</div>

<div class="layout-2">
    <div class="stack">
        <div class="card">
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Item</th><th>SKU</th><th class="num">Rate</th><th class="num">Packs</th><th class="num">Amount</th></tr></thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td><strong>{{ $item->product_name }}</strong><br><small>{{ $item->variant_label }}</small></td>
                                <td class="mono">{{ $item->sku }}</td>
                                <td class="num">@inr($item->unit_price)</td>
                                <td class="num">{{ $item->quantity }}</td>
                                <td class="num">@inr($item->line_total)</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-body" style="max-width:340px; margin-left:auto;">
                <div class="summary-row"><span>Subtotal</span><span>@inr($order->subtotal)</span></div>
                <div class="summary-row"><span>GST</span><span>@inr($order->gst_amount)</span></div>
                <div class="summary-row"><span>Shipping</span><span>@inr($order->shipping_fee)</span></div>
                <div class="summary-row total"><span>Total</span><span>@inr($order->total)</span></div>
            </div>
        </div>

        @if($order->notes)
            <div class="card card-body"><h3>Customer notes</h3><p style="margin:0; white-space:pre-line;">{{ $order->notes }}</p></div>
        @endif
    </div>

    <aside class="stack">
        <div class="card card-body">
            <h3>Update order</h3>
            <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="fields">
                @csrf @method('PATCH')
                <div class="field">
                    <label for="status">Status</label>
                    <select class="input" id="status" name="status">
                        @foreach(\App\Enums\OrderStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected($order->status === $status)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <span class="hint">Cancelling returns the packs to stock.</span>
                </div>
                <div class="field">
                    <label for="tracking_number">Courier / tracking number</label>
                    <input class="input" id="tracking_number" name="tracking_number" value="{{ $order->tracking_number }}" placeholder="e.g. DTDC Z12345678">
                </div>
                @if($order->payment_method === \App\Enums\PaymentMethod::CashOnDelivery && $order->payment_status !== \App\Enums\PaymentStatus::Paid)
                    <label class="check"><input type="checkbox" name="mark_cod_paid" value="1"> Cash collected (mark paid)</label>
                @endif
                <button class="btn btn-primary btn-block" type="submit">Save</button>
            </form>
        </div>
        <div class="card card-body">
            <h3>Customer</h3>
            <p style="margin:0;">
                <strong>{{ $order->shipping_name }}</strong><br>
                <a href="tel:{{ $order->shipping_phone }}">📞 {{ $order->shipping_phone }}</a><br>
                {{ $order->user->email }}<br><br>
                {{ $order->shipping_address }}<br>
                {{ $order->shipping_city }}, {{ $order->shipping_state }} – {{ $order->shipping_pincode }}
            </p>
            @if($order->gstin)
                <hr style="margin:12px 0;">
                <small>{{ $order->company_name }}<br>GSTIN <span class="mono">{{ $order->gstin }}</span></small>
            @endif
        </div>
        <div class="card card-body">
            <h3>Payment</h3>
            <p style="margin:0;">
                {{ $order->payment_method->label() }}<br>
                Status: <strong>{{ $order->payment_status->label() }}</strong>
                @if($order->paid_at)<br><small>Paid {{ $order->paid_at->format('d M Y, h:i A') }}</small>@endif
                @if($order->razorpay_payment_id)<br><small class="mono">{{ $order->razorpay_payment_id }}</small>@endif
            </p>
        </div>
    </aside>
</div>
@endsection
