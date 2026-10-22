@extends('layouts.app')

@section('title', 'Your Shopping Cart - Aura Store')

@section('content')
<div class="container">
    <div style="margin-bottom: 28px;">
        <h1 style="font-size: 2.2rem; font-weight: 800; margin-bottom: 6px;">Shopping Cart</h1>
        <p style="color: var(--text-muted);">Review your selected items, adjust quantities, or proceed to checkout.</p>
    </div>

    <!-- Main Cart Layout: Items on Left, Summary on Right -->
    <div class="cart-layout" id="cartContainer">
        <!-- Cart Items Column -->
        <div class="cart-items-list" id="cartItemsList" aria-live="polite">
            <!-- Rendered dynamically by JavaScript -->
        </div>

        <!-- Cart Summary Sidebar -->
        <aside class="summary-card" id="cartSummaryCard">
            <h2 class="summary-title">Order Summary</h2>

            <div class="summary-line">
                <span>Subtotal</span>
                <span id="cartSubtotal" style="color:var(--text-main); font-weight:600;">₹0.00</span>
            </div>

            <div class="summary-line" id="discountRow" style="display:none; color:#10b981;">
                <span>Promo Discount (<span id="discountLabel"></span>)</span>
                <span id="cartDiscount">-₹0.00</span>
            </div>

            <div class="summary-line">
                <span>Shipping</span>
                <span id="cartShipping" style="color:var(--text-main); font-weight:600;">₹0.00</span>
            </div>

            <div class="summary-line">
                <span>Estimated GST (18%)</span>
                <span id="cartTax" style="color:var(--text-main); font-weight:600;">₹0.00</span>
            </div>

            <!-- Coupon Code Section -->
            <div style="margin: 20px 0; padding: 14px 0; border-top: 1px solid var(--border-subtle); border-bottom: 1px solid var(--border-subtle);">
                <label for="couponInput" style="display:block; font-size:0.8rem; font-weight:600; color:var(--text-muted); margin-bottom:6px;">
                    Have a Demo Promo Code?
                </label>
                <div class="coupon-box" style="margin:0 0 8px 0;">
                    <input type="text" id="couponInput" class="coupon-input" placeholder="e.g. DEMO20">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="applyPromoCode()">Apply</button>
                </div>
                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                    <span style="font-size:0.75rem; color:var(--text-dim);">Try demo code:</span>
                    <button type="button" onclick="quickApplyPromo('DEMO20')" style="background:none; border:none; color:#818cf8; font-size:0.75rem; text-decoration:underline; cursor:pointer;">DEMO20 (-20%)</button>
                    <button type="button" onclick="quickApplyPromo('FREESHIP')" style="background:none; border:none; color:#818cf8; font-size:0.75rem; text-decoration:underline; cursor:pointer;">FREESHIP</button>
                </div>
            </div>

            <div class="summary-total">
                <span>Estimated Total</span>
                <div class="summary-total-num" id="cartTotal">₹0.00</div>
            </div>

            <div style="margin-top: 24px; display:flex; flex-direction:column; gap:12px;">
                <a href="{{ route('checkout') }}" class="btn btn-primary btn-lg btn-block" id="checkoutBtn">
                    Proceed to Checkout
                </a>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:6px;">
                    <a href="{{ route('catalog') }}" style="font-size:0.875rem; color:var(--text-muted); text-decoration:none;">
                        Continue Shopping
                    </a>
                    <button type="button" onclick="clearCart()" style="background:none; border:none; color:#f87171; font-size:0.825rem; cursor:pointer; text-decoration:underline;">
                        Clear Cart
                    </button>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection

@section('scripts')
<script>
let appliedPromo = null; // e.g. { code: 'DEMO20', rate: 0.2, type: 'percent' }

function renderCartPage() {
    const listEl = document.getElementById('cartItemsList');
    const summaryCard = document.getElementById('cartSummaryCard');
    if (!listEl) return;

    const cart = getCart();

    if (cart.length === 0) {
        listEl.innerHTML = `
            <div class="empty-state" style="grid-column: 1 / -1; width:100%;">
                <div class="empty-icon">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <h3 style="font-size: 1.4rem; font-weight:700; margin-bottom:8px;">Your cart is currently empty</h3>
                <p style="color:var(--text-muted); max-width:400px; margin:0 auto 24px;">Discover our collection of curated devices and accessories to get started.</p>
                <a href="{{ route('catalog') }}" class="btn btn-primary">Browse Products Catalog</a>
            </div>
        `;
        if (summaryCard) summaryCard.style.display = 'none';
        return;
    }

    if (summaryCard) summaryCard.style.display = 'block';

    listEl.innerHTML = cart.map(item => `
        <div class="cart-item" id="item-${item.id}">
            <img src="${item.image}" alt="${escapeHtml(item.name)}" class="cart-item-img" onerror="this.src='/images/products/headphones.jpg'">
            
            <div class="cart-item-info">
                <div class="cart-item-cat">${escapeHtml(item.category || 'Tech')}</div>
                <h3 class="cart-item-title">${escapeHtml(item.name)}</h3>
                <div class="cart-item-unit-price">${formatINR(item.price)} each</div>
            </div>

            <!-- Quantity Controls -->
            <div class="cart-qty-ctrl">
                <button type="button" class="qty-btn" onclick="updateCartQty('${item.id}', -1)" aria-label="Decrease quantity">-</button>
                <span class="qty-value">${item.quantity}</span>
                <button type="button" class="qty-btn" onclick="updateCartQty('${item.id}', 1)" aria-label="Increase quantity">+</button>
            </div>

            <!-- Line Total -->
            <div class="cart-item-total">
                ${formatINR(parseFloat(item.price) * item.quantity)}
            </div>

            <!-- Remove Action -->
            <button type="button" onclick="removeFromCart('${item.id}')" class="btn btn-sm btn-danger" style="padding:6px 10px;" title="Remove item">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
        </div>
    `).join('');

    updateCartSummaryCalculations();
}

function updateCartSummaryCalculations() {
    const cart = getCart();
    const subtotal = cart.reduce((sum, item) => sum + (parseFloat(item.price) * (item.quantity || 1)), 0);

    let discount = 0;
    if (appliedPromo) {
        if (appliedPromo.type === 'percent') {
            discount = subtotal * appliedPromo.rate;
        } else if (appliedPromo.type === 'fixed') {
            discount = appliedPromo.amount;
        }
    }

    // Free delivery on orders above ₹2,000, otherwise ₹149.00
    let shipping = subtotal > 2000 ? 0 : 149.00;
    if (appliedPromo && appliedPromo.code === 'FREESHIP') {
        shipping = 0;
    }

    const taxableAmount = Math.max(0, subtotal - discount);
    const tax = taxableAmount * 0.18; // 18% GST
    const total = taxableAmount + shipping + tax;

    // Save calculations for checkout page synchronization
    localStorage.setItem('demo_cart_summary', JSON.stringify({
        subtotal,
        discount,
        shipping,
        tax,
        total,
        promo: appliedPromo ? appliedPromo.code : null
    }));

    document.getElementById('cartSubtotal').textContent = formatINR(subtotal);
    
    const discountRow = document.getElementById('discountRow');
    if (discount > 0) {
        discountRow.style.display = 'flex';
        document.getElementById('discountLabel').textContent = appliedPromo.code;
        document.getElementById('cartDiscount').textContent = `-${formatINR(discount)}`;
    } else {
        discountRow.style.display = 'none';
    }

    document.getElementById('cartShipping').textContent = shipping === 0 ? 'FREE' : formatINR(shipping);
    document.getElementById('cartTax').textContent = formatINR(tax);
    document.getElementById('cartTotal').textContent = formatINR(total);
}

function applyPromoCode() {
    const code = (document.getElementById('couponInput').value || '').trim().toUpperCase();
    if (!code) {
        showToast('Please enter a coupon code.', 'warning');
        return;
    }

    if (code === 'DEMO20') {
        appliedPromo = { code: 'DEMO20', rate: 0.20, type: 'percent' };
        showToast('Promo code DEMO20 applied! 20% discount added.', 'success');
    } else if (code === 'FREESHIP') {
        appliedPromo = { code: 'FREESHIP', rate: 0, type: 'freeship' };
        showToast('Promo code FREESHIP applied! Free shipping enabled.', 'success');
    } else {
        showToast('Invalid promo code. Try DEMO20 or FREESHIP.', 'warning');
        return;
    }

    updateCartSummaryCalculations();
}

function quickApplyPromo(code) {
    document.getElementById('couponInput').value = code;
    applyPromoCode();
}
</script>
@endsection
