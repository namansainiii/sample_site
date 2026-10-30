<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') · @endif{{ config('store.name') }}</title>
    <meta name="description" content="@yield('description', 'Buy bolts, nuts, screws, washers, anchors and industrial hardware online at wholesale prices. GST invoice, bulk discounts and fast delivery across India.')">
    <link rel="icon" href="{{ asset('images/categories/nuts.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/store.css') }}?v=4">
    @stack('head')
</head>
<body>
    <div class="topbar">
        <div class="container">
            <div class="perks">
                <span>GST invoice on every order</span>
                <span>Bulk pricing on 10+ packs</span>
                <span>Free shipping above @inr(config('store.free_shipping_threshold'))</span>
            </div>
            <div>Sales: <a href="tel:{{ preg_replace('/\s+/', '', config('store.phone')) }}">{{ config('store.phone') }}</a></div>
        </div>
    </div>

    <header class="header">
        <div class="container header-inner">
            <a href="{{ route('home') }}" class="brand" aria-label="{{ config('store.name') }} home">
                <span class="brand-mark">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 2 20.66 7v10L12 22 3.34 17V7z"/><circle cx="12" cy="12" r="3.5"/></svg>
                </span>
                @php([$first, $rest] = array_pad(explode(' ', config('store.name'), 2), 2, ''))
                <span>{{ $first }} <b>{{ $rest }}</b></span>
            </a>

            <form class="search" action="{{ route('shop') }}" method="GET" role="search">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search M8 bolt, SS nut, DIN 912, SKU…" aria-label="Search products">
                <button type="submit">Search</button>
            </form>

            <div class="header-actions">
                <a href="{{ route('quotes.create') }}" class="icon-link" title="Bulk quote">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6M7 3h7l5 5v12a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/></svg>
                    <span class="txt">Bulk Quote</span>
                </a>
                @auth
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="icon-link">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 13h6V4H4zm10 7h6v-9h-6zM4 20h6v-4H4zm10-12h6V4h-6z"/></svg>
                            <span class="txt">Admin</span>
                        </a>
                    @endif
                    <a href="{{ route('account.orders') }}" class="icon-link">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                        <span class="txt">My Orders</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-ghost btn-sm" type="submit">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="icon-link">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                        <span class="txt">Login</span>
                    </a>
                @endauth
                <a href="{{ route('cart.index') }}" class="icon-link" aria-label="Cart">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3h2l2.4 12.2a2 2 0 0 0 2 1.8h8.2a2 2 0 0 0 2-1.6L21 8H6"/><circle cx="10" cy="21" r="1"/><circle cx="18" cy="21" r="1"/></svg>
                    <span class="cart-badge" id="cartCount" @if(!$cartCount) hidden @endif>{{ $cartCount }}</span>
                    <span class="txt">Cart</span>
                </a>
            </div>
        </div>
        <nav class="catnav" aria-label="Categories">
            <div class="container">
                <ul>
                    <li><a href="{{ route('shop') }}" @class(['active' => request()->routeIs('shop')])>All Products</a></li>
                    @foreach($navCategories as $navCategory)
                        <li><a href="{{ route('shop.category', $navCategory) }}" @class(['active' => request()->route('category')?->slug === $navCategory->slug])>{{ $navCategory->name }}</a></li>
                    @endforeach
                    <li class="highlight"><a href="{{ route('cart.index') }}#quick-order">⚡ Quick Order by SKU</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        @hasSection('flush')
            @include('partials.flash', ['wrap' => true])
            @yield('flush')
        @else
            <div class="container" style="padding-top:20px;">
                @include('partials.flash')
            </div>
            @yield('content')
        @endif
    </main>

    <footer class="footer">
        <div class="container">
            <div class="cols">
                <div>
                    <div class="brand">{{ config('store.name') }}</div>
                    <p>{{ config('store.tagline') }} Trusted by fabricators, contractors, electricians and OEMs.</p>
                    <p class="mono">GSTIN: {{ config('store.gstin') }}</p>
                </div>
                <div>
                    <h4>Shop</h4>
                    <ul>
                        @foreach($navCategories->take(6) as $footCategory)
                            <li><a href="{{ route('shop.category', $footCategory) }}">{{ $footCategory->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h4>For Business</h4>
                    <ul>
                        <li><a href="{{ route('quotes.create') }}">Request bulk quote</a></li>
                        <li><a href="{{ route('cart.index') }}#quick-order">Quick order by SKU</a></li>
                        <li><a href="{{ route('register') }}">Open a GST account</a></li>
                        <li><a href="{{ route('account.orders') }}">Track your order</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Contact</h4>
                    <ul>
                        <li>{{ config('store.address') }}</li>
                        <li><a href="tel:{{ preg_replace('/\s+/', '', config('store.phone')) }}">{{ config('store.phone') }}</a></li>
                        <li><a href="mailto:{{ config('store.email') }}">{{ config('store.email') }}</a></li>
                        <li>Mon–Sat, 9:30 AM – 7:00 PM</li>
                    </ul>
                </div>
            </div>
            <div class="legal">
                <span>© {{ date('Y') }} {{ config('store.name') }}. All rights reserved.</span>
                <span>Prices exclude GST unless stated · Secure payments via Razorpay</span>
            </div>
        </div>
    </footer>

    <a class="wa-float no-print" href="https://wa.me/{{ config('store.whatsapp') }}?text={{ rawurlencode('Hi '.config('store.name').', I have an enquiry.') }}" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><path d="M17.5 14.4c-.3-.1-1.8-.9-2-1s-.5-.1-.7.1-.8 1-.9 1.2-.3.2-.6.1a8.2 8.2 0 0 1-2.4-1.5 9 9 0 0 1-1.7-2.1c-.2-.3 0-.5.1-.6l.5-.5.3-.5v-.5l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6a1.1 1.1 0 0 0-.8.4 3.4 3.4 0 0 0-1.1 2.5 5.9 5.9 0 0 0 1.2 3.1 13.4 13.4 0 0 0 5.2 4.6c1.9.8 2.7.9 3.6.7a3.1 3.1 0 0 0 2-1.4 2.5 2.5 0 0 0 .2-1.4c-.1-.1-.3-.2-.6-.3zM12 21.8a9.8 9.8 0 0 1-5-1.4l-.4-.2-3.7 1 1-3.6-.2-.4A9.8 9.8 0 1 1 12 21.8zm0-21.6A11.8 11.8 0 0 0 1.9 18L.2 24l6.2-1.6A11.8 11.8 0 1 0 12 .2z"/></svg>
    </a>
    <div class="toast" id="toast" role="status" aria-live="polite"></div>

    <script src="{{ asset('js/store.js') }}?v=1" defer></script>
    @stack('scripts')
</body>
</html>
