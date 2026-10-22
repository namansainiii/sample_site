<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Aura Store - Modern Prototype & Frontend Demo featuring catalog, cart, login, and instant checkout.">
    <title>@yield('title', 'Aura Store - Next-Gen Commerce UI')</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- Pure Vanilla Modern Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/demo.css') }}">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('demo_theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);

            // Immediate auth lockdown: only /login and /register are accessible when not signed in
            const path = window.location.pathname;
            const isPublicPage = path === '/login' || path === '/register';
            const user = localStorage.getItem('demo_user');
            if (!isPublicPage && !user) {
                window.location.replace('/login');
            }
        })();
    </script>
    @yield('styles')
</head>
<body>
    <!-- Top Global Header -->
    <header class="header">
        <div class="container">
            <div class="header-inner">
                <!-- Brand Logo (links to /login when logged out, /catalog when logged in) -->
                <a href="/login" class="brand" id="brandLogoLink">
                    <div class="brand-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>
                        </svg>
                    </div>
                    <span>Aura<span style="color:#06b6d4;">Store</span></span>
                    <span class="brand-tag">Demo UI</span>
                </a>

                <!-- Navigation Links (Populated dynamically by demo.js based on role) -->
                <nav>
                    <ul class="nav-links" id="mainNavLinks">
                        <!-- Populated when user is logged in -->
                    </ul>
                </nav>

                <!-- Actions: Cart, Theme Toggle & Auth -->
                <div class="nav-actions">
                    <!-- Theme Mode Toggle (Default: Light) -->
                    <button type="button" id="themeToggleBtn" onclick="toggleTheme()" class="theme-toggle-btn" aria-label="Toggle light or dark theme" title="Switch Theme (Light / Dark)">
                        <svg class="theme-icon-sun" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:inline-block; vertical-align:middle;">
                            <circle cx="12" cy="12" r="5"></circle>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72 1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"></path>
                        </svg>
                        <svg class="theme-icon-moon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none; vertical-align:middle;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"></path>
                        </svg>
                        <span id="themeLabelText">Light</span>
                    </button>

                    <a href="{{ route('cart') }}" class="cart-nav-btn" id="cartNavBtn" title="View Cart" style="display:none;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span>Cart</span>
                        <span id="cartNavBadge" class="cart-count-badge">0</span>
                    </a>

                    <!-- User Auth Container (populated by demo.js) -->
                    <div id="userNavContainer" style="display:flex; align-items:center; gap:8px;">
                        <a href="{{ route('login') }}" class="btn btn-sm btn-secondary">Sign In</a>
                        <a href="{{ route('register') }}" class="btn btn-sm btn-primary">Register</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Toast Notification Container -->
    <div id="toastContainer" class="toast-container" aria-live="polite"></div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-inner">
                <div>
                    <div style="font-family:'Space Grotesk', sans-serif; font-weight:700; font-size:1.15rem; color:var(--text-main); margin-bottom:4px;">
                        Aura<span style="color:#06b6d4;">Store</span>
                    </div>
                    <p class="footer-text">Demonstration Frontend UI | No database connection required | Interactive Local State</p>
                </div>
                <div id="footerNavLinks" style="display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
                    <a href="{{ route('login') }}" class="nav-link" style="padding:4px 8px; font-size:0.85rem;">Sign In</a>
                    <a href="{{ route('register') }}" class="nav-link" style="padding:4px 8px; font-size:0.85rem;">Register</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- App Scripts -->
    <script src="{{ asset('js/demo.js') }}"></script>
    @yield('scripts')
</body>
</html>
