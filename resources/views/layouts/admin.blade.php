<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>@yield('title', 'Dashboard') · Admin · {{ config('store.name') }}</title>
    <link rel="icon" href="{{ asset('images/categories/nuts.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/store.css') }}?v=4">
</head>
<body>
@php
    $newQuotes = \App\Models\QuoteRequest::where('status', 'new')->count();
    $pendingOrders = \App\Models\Order::where('status', 'pending')->count();
@endphp
<div class="admin">
    <aside class="admin-side">
        <a href="{{ route('admin.dashboard') }}" class="brand">{{ config('store.name') }}</a>
        <nav>
            <div class="sep">Overview</div>
            <a href="{{ route('admin.dashboard') }}" @class(['active' => request()->routeIs('admin.dashboard')])>📊 Dashboard</a>
            <div class="sep">Sales</div>
            <a href="{{ route('admin.orders.index') }}" @class(['active' => request()->routeIs('admin.orders.*')])>🧾 Orders @if($pendingOrders)<span class="pill">{{ $pendingOrders }}</span>@endif</a>
            <a href="{{ route('admin.quotes.index') }}" @class(['active' => request()->routeIs('admin.quotes.*')])>💬 Quote requests @if($newQuotes)<span class="pill">{{ $newQuotes }}</span>@endif</a>
            <div class="sep">Catalogue</div>
            <a href="{{ route('admin.products.index') }}" @class(['active' => request()->routeIs('admin.products.*')])>🔩 Products</a>
            <a href="{{ route('admin.categories.index') }}" @class(['active' => request()->routeIs('admin.categories.*')])>🗂️ Categories</a>
            <div class="sep">Store</div>
            <a href="{{ route('home') }}" target="_blank">🛒 View storefront ↗</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost btn-block" style="justify-content:flex-start; color:#cbd5e1; margin-top:4px;">🚪 Logout</button>
            </form>
        </nav>
    </aside>
    <main class="admin-main">
        @include('partials.flash')
        @yield('content')
    </main>
</div>
<div class="toast" id="toast" role="status" aria-live="polite"></div>
<script src="{{ asset('js/store.js') }}?v=1" defer></script>
@stack('scripts')
</body>
</html>
