@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
<div class="admin-top"><h1>Orders</h1></div>

<form class="filters-bar" method="GET">
    <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Order no, name or phone">
    <select class="input" name="status" onchange="this.form.submit()">
        <option value="">All statuses</option>
        @foreach(\App\Enums\OrderStatus::cases() as $status)
            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
        @endforeach
    </select>
    <button class="btn btn-dark" type="submit">Filter</button>
</form>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Order</th><th>Customer</th><th>Placed</th><th>Items</th><th>Payment</th><th>Status</th><th class="num">Total</th></tr></thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td><a class="mono" href="{{ route('admin.orders.show', $order) }}"><strong>{{ $order->order_number }}</strong></a></td>
                        <td>{{ $order->shipping_name }}<br><small>{{ $order->shipping_phone }} · {{ $order->shipping_city }}</small></td>
                        <td>{{ $order->created_at->format('d M Y') }}<br><small>{{ $order->created_at->format('h:i A') }}</small></td>
                        <td>{{ $order->items_count }}</td>
                        <td>{{ $order->payment_method === \App\Enums\PaymentMethod::CashOnDelivery ? 'COD' : 'Online' }}</td>
                        <td>@include('partials.order-status')</td>
                        <td class="num"><strong>@inr($order->total)</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $orders->links() }}
@endsection
