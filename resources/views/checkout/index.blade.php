@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
@php($user = auth()->user())
<div class="container">
    <h1>Checkout</h1>

    <form method="POST" action="{{ route('checkout.store') }}" class="layout-2">
        @csrf
        <div class="stack">
            <div class="card">
                <div class="card-head"><h3>1. Delivery address</h3></div>
                <div class="card-body form-grid">
                    <div class="field">
                        <label for="shipping_name">Full name</label>
                        <input class="input" id="shipping_name" name="shipping_name" required value="{{ old('shipping_name', $prefill->shipping_name ?? $user->name) }}">
                    </div>
                    <div class="field">
                        <label for="shipping_phone">Mobile number</label>
                        <input class="input" id="shipping_phone" name="shipping_phone" required inputmode="numeric" maxlength="10" value="{{ old('shipping_phone', $prefill->shipping_phone ?? $user->phone) }}">
                    </div>
                    <div class="field span-2">
                        <label for="shipping_address">Address (shop / plot no., street, area)</label>
                        <input class="input" id="shipping_address" name="shipping_address" required value="{{ old('shipping_address', $prefill->shipping_address ?? '') }}">
                    </div>
                    <div class="field">
                        <label for="shipping_city">City</label>
                        <input class="input" id="shipping_city" name="shipping_city" required value="{{ old('shipping_city', $prefill->shipping_city ?? '') }}">
                    </div>
                    <div class="field">
                        <label for="shipping_pincode">PIN code</label>
                        <input class="input" id="shipping_pincode" name="shipping_pincode" required inputmode="numeric" maxlength="6" value="{{ old('shipping_pincode', $prefill->shipping_pincode ?? '') }}">
                    </div>
                    <div class="field span-2">
                        <label for="shipping_state">State</label>
                        <select class="input" id="shipping_state" name="shipping_state" required>
                            <option value="">Select state</option>
                            @foreach(config('store.states') as $state)
                                <option @selected(old('shipping_state', $prefill->shipping_state ?? '') === $state)>{{ $state }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-head"><h3>2. GST details <small class="muted">(optional, for business purchases)</small></h3></div>
                <div class="card-body form-grid">
                    <div class="field">
                        <label for="company_name">Business name</label>
                        <input class="input" id="company_name" name="company_name" value="{{ old('company_name', $prefill->company_name ?? $user->company_name) }}">
                    </div>
                    <div class="field">
                        <label for="gstin">GSTIN</label>
                        <input class="input mono" id="gstin" name="gstin" maxlength="15" style="text-transform:uppercase" value="{{ old('gstin', $prefill->gstin ?? $user->gstin) }}" placeholder="22AAAAA0000A1Z5">
                        <span class="hint">Your GSTIN appears on the tax invoice so you can claim input credit.</span>
                    </div>
                    <div class="field span-2">
                        <label for="notes">Order notes</label>
                        <textarea class="input" id="notes" name="notes" rows="2" placeholder="Delivery timing, landmark, packing instructions…">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-head"><h3>3. Payment</h3></div>
                <div class="card-body">
                    @if($onlinePaymentEnabled)
                        <label class="pay-option">
                            <input type="radio" name="payment_method" value="razorpay" @checked(old('payment_method', 'razorpay') === 'razorpay')>
                            <div><strong>Pay online</strong><br><small>UPI, debit/credit card, netbanking, wallets. Secured by Razorpay.</small></div>
                        </label>
                    @endif
                    <label class="pay-option">
                        <input type="radio" name="payment_method" value="cod" @checked(old('payment_method', $onlinePaymentEnabled ? 'razorpay' : 'cod') === 'cod')>
                        <div><strong>Cash on Delivery</strong><br><small>Pay in cash or UPI when your order arrives. We call to confirm before dispatch.</small></div>
                    </label>
                </div>
            </div>
        </div>

        <aside class="summary card card-body">
            <h3>Your order ({{ $lines->sum('quantity') }} packs)</h3>
            @foreach($lines as $line)
                <div class="summary-row" style="gap:10px;">
                    <span style="min-width:0;">{{ $line['variant']->product->name }}<br><small>{{ $line['variant']->label() }} × {{ $line['quantity'] }}</small></span>
                    <span>@inr($line['line_total'])</span>
                </div>
            @endforeach
            <hr style="margin:10px 0;">
            <div class="summary-row"><span>Subtotal</span><span>@inr($totals['subtotal'])</span></div>
            <div class="summary-row"><span>GST</span><span>@inr($totals['gst'])</span></div>
            <div class="summary-row"><span>Shipping</span><span>{{ $totals['shipping'] > 0 ? \App\Support\Money::inr($totals['shipping']) : 'FREE' }}</span></div>
            <div class="summary-row total"><span>Total payable</span><span>@inr($totals['total'])</span></div>
            <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top:16px;">Place order</button>
            <small style="display:block; margin-top:10px; text-align:center;">By placing the order you agree to our terms of sale.</small>
        </aside>
    </form>
</div>
@endsection
