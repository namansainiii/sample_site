@extends('layouts.app')

@section('title', 'Complete payment')

@section('content')
<div class="container auth-wrap">
    <div class="card auth-card card-body" style="text-align:center;">
        <span class="eyebrow">Order {{ $order->order_number }}</span>
        <h2>Complete your payment</h2>
        <p class="muted">Amount payable</p>
        <div class="display" style="font-size:2.6rem;">@inr($order->total)</div>
        <button id="payBtn" class="btn btn-primary btn-lg btn-block" style="margin-top:18px;">Pay securely now</button>
        <a href="{{ route('account.orders.show', $order) }}" class="btn btn-ghost btn-block" style="margin-top:6px;">I'll pay later</a>
        <small class="muted" style="display:block; margin-top:12px;">UPI · Cards · Netbanking · Wallets — powered by Razorpay</small>

        <form id="verifyForm" method="POST" action="{{ route('checkout.verify', $order) }}" hidden>
            @csrf
            <input type="hidden" name="razorpay_payment_id">
            <input type="hidden" name="razorpay_order_id">
            <input type="hidden" name="razorpay_signature">
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(function () {
    const form = document.getElementById('verifyForm');
    const options = {
        key: @json($razorpayKey),
        amount: @json((int) round($order->total * 100)),
        currency: 'INR',
        name: @json(config('store.name')),
        description: @json('Order '.$order->order_number),
        order_id: @json($order->razorpay_order_id),
        prefill: {
            name: @json($order->shipping_name),
            email: @json($order->user->email),
            contact: @json($order->shipping_phone),
        },
        theme: { color: '#f59e0b' },
        handler: function (response) {
            form.razorpay_payment_id.value = response.razorpay_payment_id;
            form.razorpay_order_id.value = response.razorpay_order_id;
            form.razorpay_signature.value = response.razorpay_signature;
            form.submit();
        },
    };
    const open = () => {
        const rzp = new Razorpay(options);
        rzp.on('payment.failed', (r) => window.storeToast && storeToast('Payment failed: ' + (r.error.description || 'please try again')));
        rzp.open();
    };
    document.getElementById('payBtn').addEventListener('click', open);
    window.addEventListener('load', open);
})();
</script>
@endpush
