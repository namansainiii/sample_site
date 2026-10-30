@extends('layouts.admin')

@section('content')
<div class="admin-top">
    <div>
        <h1>Dashboard</h1>
        <small>{{ now()->format('l, d M Y') }}</small>
    </div>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">+ Add product</a>
</div>

<div class="stat-grid">
    <div class="card stat"><div class="label">Sales today</div><div class="value">@inr($stats['revenue_today'])</div></div>
    <div class="card stat"><div class="label">Sales this month</div><div class="value">@inr($stats['revenue_month'])</div></div>
    <a href="{{ route('admin.orders.index') }}" class="card stat"><div class="label">Orders to fulfil</div><div class="value">{{ $stats['orders_open'] }}</div></a>
    <a href="{{ route('admin.quotes.index', ['status' => 'new']) }}" class="card stat"><div class="label">New quote requests</div><div class="value">{{ $stats['quotes_new'] }}</div></a>
</div>

<div class="grid" style="grid-template-columns:minmax(0,1.6fr) minmax(0,1fr); margin-bottom:20px;">
    <div class="card">
        <div class="card-head"><h3>Sales, last 14 days</h3><small>excl. cancelled</small></div>
        <div class="card-body">
            @php($max = max(1, $chart->max()))
            <div class="bars">
                @foreach($chart as $day => $amount)
                    <div class="bar" title="{{ $day }}: {{ \App\Support\Money::inr($amount) }}">
                        <span style="height: {{ max(1, round($amount / $max * 140)) }}px"></span>
                        <small class="muted">{{ \Illuminate\Support\Str::before($day, ' ') }}</small>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head"><h3>⚠️ Low stock</h3><a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-ghost">All products</a></div>
        <div class="table-wrap">
            <table class="table">
                @forelse($lowStock as $variant)
                    <tr>
                        <td><a href="{{ route('admin.products.edit', $variant->product) }}"><strong>{{ $variant->product->name }}</strong></a><br><small>{{ $variant->size }} · <span class="mono">{{ $variant->sku }}</span></small></td>
                        <td class="num"><span class="badge {{ $variant->stock === 0 ? 'badge-bad' : 'badge-warn' }}">{{ $variant->stock }} left</span></td>
                    </tr>
                @empty
                    <tr><td class="muted">All sizes are well stocked.</td></tr>
                @endforelse
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-head"><h3>Recent orders</h3><a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-ghost">View all</a></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Status</th><th class="num">Total</th></tr></thead>
            <tbody>
                @forelse($recentOrders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order) }}" class="mono"><strong>{{ $order->order_number }}</strong></a></td>
                        <td>{{ $order->shipping_name }}<br><small>{{ $order->shipping_city }}</small></td>
                        <td>{{ $order->created_at->format('d M, h:i A') }}</td>
                        <td>@include('partials.order-status')</td>
                        <td class="num"><strong>@inr($order->total)</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">No orders yet. Share your store link to get the first one!</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
