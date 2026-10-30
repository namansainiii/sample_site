@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="container" style="padding-bottom:40px;">
    <div class="section-head">
        <div>
            <h1 style="margin:0;">My orders</h1>
            <p>Track deliveries and download GST invoices.</p>
        </div>
        <a href="{{ route('shop') }}" class="btn btn-outline btn-sm">Continue shopping</a>
    </div>

    @if($orders->isEmpty())
        <div class="card empty">
            <h3>No orders yet</h3>
            <p>When you place an order it will appear here.</p>
            <a class="btn btn-primary" href="{{ route('shop') }}">Browse products</a>
        </div>
    @else
        <div class="card" style="overflow:hidden;">
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Order</th><th>Date</th><th>Items</th><th>Status</th><th class="num">Total</th><th></th></tr></thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td><a href="{{ route('account.orders.show', $order) }}"><strong class="mono">{{ $order->order_number }}</strong></a></td>
                                <td>{{ $order->created_at->format('d M Y') }}</td>
                                <td>{{ $order->items_count }}</td>
                                <td>@include('partials.order-status')</td>
                                <td class="num"><strong>@inr($order->total)</strong></td>
                                <td class="num"><a class="btn btn-sm btn-outline" href="{{ route('account.orders.show', $order) }}">View</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        {{ $orders->links() }}
    @endif
</div>
@endsection
