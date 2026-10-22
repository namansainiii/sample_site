@extends('layouts.app')

@section('title', 'Secure Checkout & Payment - Aura Store')

@section('content')
<div class="container">
    <div style="margin-bottom: 28px;">
        <h1 style="font-size: 2.2rem; font-weight: 800; margin-bottom: 6px;">Checkout & Payment</h1>
        <p style="color: var(--text-muted);">Complete your order with simulated secure processing in Indian Rupees (₹ INR).</p>
    </div>

    <div class="checkout-layout">
        <!-- Left: Shipping & Payment Information -->
        <div>
            <!-- Step 1: Shipping Details -->
            <section class="checkout-section">
                <div class="checkout-section-header">
                    <div style="display:flex; align-items:center;">
                        <span class="step-number">1</span>
                        <h2 style="font-size: 1.25rem; font-weight:700;">Shipping Address</h2>
                    </div>
                    <button type="button" class="btn btn-sm btn-quickfill" onclick="quickFillShipping()">
                        Quick Fill Address
                    </button>
                </div>

                <form id="shippingForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="shipName" class="form-label">Recipient Full Name *</label>
                            <input type="text" id="shipName" class="form-control" placeholder="Alex Vance" required>
                        </div>
                        <div class="form-group">
                            <label for="shipEmail" class="form-label">Email for Tracking *</label>
                            <input type="email" id="shipEmail" class="form-control" placeholder="alex.vance@demo.in" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="shipAddress" class="form-label">Street Address *</label>
                        <input type="text" id="shipAddress" class="form-control" placeholder="Flat 402, Lotus Heights, Bandra West" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="shipCity" class="form-label">City *</label>
                            <input type="text" id="shipCity" class="form-control" placeholder="Mumbai" required>
                        </div>
                        <div class="form-group">
                            <label for="shipPostal" class="form-label">State & PIN Code *</label>
                            <input type="text" id="shipPostal" class="form-control" placeholder="Maharashtra - 400050" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="shipPhone" class="form-label">Contact Mobile (+91) *</label>
                        <input type="tel" id="shipPhone" class="form-control" placeholder="+91 98201 92834" required>
                    </div>
                </form>
            </section>

            <!-- Step 2: Delivery Options -->
            <section class="checkout-section">
                <div class="checkout-section-header">
                    <div style="display:flex; align-items:center;">
                        <span class="step-number">2</span>
                        <h2 style="font-size: 1.25rem; font-weight:700;">Delivery Method</h2>
                    </div>
                </div>

                <div style="display:flex; flex-direction:column; gap:12px;">
                    <label class="form-check" style="padding:14px; background:var(--bg-input); border-radius:var(--radius-md); border:1px solid var(--border-subtle); display:flex; justify-content:space-between; align-items:center;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <input type="radio" name="shippingSpeed" value="0" checked onchange="handleShippingSpeedChange(0, 'Standard Delivery')">
                            <div>
                                <strong style="display:block; color:var(--text-main);">Standard Delivery (3-5 business days)</strong>
                                <span style="font-size:0.75rem; color:var(--text-dim);">Free on orders above ₹2,000</span>
                            </div>
                        </div>
                        <span style="font-weight:700; color:#10b981;">FREE</span>
                    </label>

                    <label class="form-check" style="padding:14px; background:var(--bg-input); border-radius:var(--radius-md); border:1px solid var(--border-subtle); display:flex; justify-content:space-between; align-items:center;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <input type="radio" name="shippingSpeed" value="299" onchange="handleShippingSpeedChange(299, 'Express Priority Air')">
                            <div>
                                <strong style="display:block; color:var(--text-main);">Express Priority Air (1-2 business days)</strong>
                                <span style="font-size:0.75rem; color:var(--text-dim);">Priority bluedart air express</span>
                            </div>
                        </div>
                        <span style="font-weight:700; color:var(--text-main);">₹299.00</span>
                    </label>
                </div>
            </section>

            <!-- Step 3: Payment Method -->
            <section class="checkout-section">
                <div class="checkout-section-header">
                    <div style="display:flex; align-items:center;">
                        <span class="step-number">3</span>
                        <h2 style="font-size: 1.25rem; font-weight:700;">Payment Method (India)</h2>
                    </div>
                    <button type="button" class="btn btn-sm btn-quickfill" onclick="quickFillPayment()" id="quickPaymentBtn">
                        Quick Fill Payment
                    </button>
                </div>

                <!-- Payment Method Switcher -->
                <div class="payment-tabs">
                    <div class="payment-tab active" onclick="switchPaymentMethod('upi', this)">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        <span>UPI / QR</span>
                    </div>
                    <div class="payment-tab" onclick="switchPaymentMethod('card', this)">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/></svg>
                        <span>Card (RuPay/Visa)</span>
                    </div>
                    <div class="payment-tab" onclick="switchPaymentMethod('cod', this)">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <span>Cash on Delivery</span>
                    </div>
                </div>

                <!-- UPI Container -->
                <div id="upiPaymentBox" style="padding: 16px; background:var(--bg-input); border-radius:var(--radius-md); border:1px solid var(--border-subtle);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <span style="font-size:0.85rem; font-weight:600; color:var(--text-main);">Pay via UPI Apps</span>
                        <span style="font-size:0.75rem; color:#10b981;">GPay, PhonePe, Paytm, BHIM</span>
                    </div>
                    <div class="form-group" style="margin-bottom:12px;">
                        <label for="upiId" class="form-label">Virtual Payment Address (VPA / UPI ID)</label>
                        <div style="position:relative;">
                            <input type="text" id="upiId" class="form-control" placeholder="alex.vance@okhdfcbank" value="alex.vance@okaxis">
                            <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); font-size:0.75rem; color:#10b981; font-weight:700;">VERIFIED</span>
                        </div>
                    </div>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('upiId').value='9820192834@paytm'">Paytm UPI</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('upiId').value='alex.vance@ybl'">PhonePe UPI</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('upiId').value='alex@okhdfcbank'">Google Pay</button>
                    </div>
                </div>

                <!-- Credit/Debit Card Container -->
                <div id="cardPaymentBox" style="display:none;">
                    <div class="form-group">
                        <label for="cardNumber" class="form-label">Card Number (RuPay, Visa, Mastercard)</label>
                        <div style="position:relative;">
                            <input type="text" id="cardNumber" class="form-control" placeholder="4532 8920 1192 8472" maxlength="19">
                            <span style="position:absolute; right:14px; top:50%; transform:translateY(-50%); color:var(--text-dim); font-size:0.75rem; font-weight:700;">RuPay / VISA</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="cardExpiry" class="form-label">Expiration Date</label>
                            <input type="text" id="cardExpiry" class="form-control" placeholder="MM/YY" maxlength="5">
                        </div>
                        <div class="form-group">
                            <label for="cardCvv" class="form-label">CVV</label>
                            <input type="password" id="cardCvv" class="form-control" placeholder="742" maxlength="4">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cardHolder" class="form-label">Cardholder Name</label>
                        <input type="text" id="cardHolder" class="form-control" placeholder="Alex Vance">
                    </div>
                </div>

                <!-- COD Container -->
                <div id="codBox" style="display:none; text-align:center; padding:24px; background:var(--bg-input); border-radius:var(--radius-md);">
                    <p style="color:var(--text-muted); margin-bottom:8px;">Pay with cash or UPI upon verified doorstep delivery.</p>
                    <span style="font-size:0.8rem; color:#10b981;">No advance deposit needed for demo.</span>
                </div>
            </section>
        </div>

        <!-- Right: Checkout Order Summary -->
        <aside class="summary-card">
            <h2 class="summary-title">Summary & Items</h2>

            <!-- Items Thumbnail List -->
            <div id="checkoutItemsList" style="max-height: 240px; overflow-y:auto; margin-bottom: 20px; display:flex; flex-direction:column; gap:12px; padding-right:4px;">
                <!-- Populated by JS -->
            </div>

            <div class="summary-line">
                <span>Subtotal</span>
                <span id="chkSubtotal" style="color:var(--text-main); font-weight:600;">₹0.00</span>
            </div>

            <div class="summary-line" id="chkDiscountRow" style="display:none; color:#10b981;">
                <span>Discount</span>
                <span id="chkDiscount">-₹0.00</span>
            </div>

            <div class="summary-line">
                <span>Selected Delivery</span>
                <span id="chkShipping" style="color:var(--text-main); font-weight:600;">FREE</span>
            </div>

            <div class="summary-line">
                <span>Estimated GST (18%)</span>
                <span id="chkTax" style="color:var(--text-main); font-weight:600;">₹0.00</span>
            </div>

            <div class="summary-total">
                <span>Total Amount</span>
                <div class="summary-total-num" id="chkTotal">₹0.00</div>
            </div>

            <button type="button" class="btn btn-primary btn-lg btn-block" style="margin-top:24px;" onclick="handlePlaceOrder()" id="placeOrderBtn">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                Place Demo Order (₹)
            </button>

            <!-- Trust badges -->
            <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border-subtle); display:flex; flex-direction:column; gap:8px;">
                <div style="display:flex; align-items:center; gap:8px; font-size:0.78rem; color:var(--text-muted);">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="color:#10b981;"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    256-Bit SSL Encrypted Indian Gateway Simulation
                </div>
                <div style="display:flex; align-items:center; gap:8px; font-size:0.78rem; color:var(--text-muted);">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="color:#38bdf8;"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    Instant GST Compliant Invoice & Confirmation
                </div>
            </div>
        </aside>
    </div>
</div>

<!-- Order Confirmation Modal -->
<dialog id="orderSuccessModal" closedby="any" aria-labelledby="orderSuccessTitle">
    <div class="dialog-content" style="text-align: center; max-width: 500px;">
        <div class="success-check-icon">
            <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
        </div>

        <h2 id="orderSuccessTitle" style="font-size: 1.6rem; font-weight:800; margin-bottom: 8px;">Order Confirmed!</h2>
        <p style="color:var(--text-muted); font-size:0.925rem; margin-bottom:20px;">
            Thank you for exploring the Aura Demo Storefront. Your simulated order has been registered in Indian Rupees (₹).
        </p>

        <!-- Order Summary Receipt Box -->
        <div style="background:var(--bg-input); border:1px solid var(--border-subtle); border-radius:var(--radius-md); padding:16px; margin-bottom:24px; text-align:left; font-size:0.875rem;">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <span style="color:var(--text-dim);">Order Number:</span>
                <strong id="confirmedOrderNum" style="color:#38bdf8; font-family:'Space Grotesk', sans-serif;">#AURA-84920</strong>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <span style="color:var(--text-dim);">Estimated Delivery:</span>
                <span id="confirmedDeliveryDate" style="color:var(--text-main);">4-5 Business Days</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <span style="color:var(--text-dim);">Payment Mode:</span>
                <span id="confirmedPaymentMethod" style="color:var(--text-main);">UPI (alex.vance@okaxis)</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding-top:8px; border-top:1px dashed var(--border-subtle);">
                <strong style="color:var(--text-main);">Total Paid (₹):</strong>
                <strong id="confirmedTotal" style="color:#10b981; font-size:1.05rem;">₹0.00</strong>
            </div>
        </div>

        <div style="display:flex; gap:12px; justify-content:center;">
            <a href="{{ route('catalog') }}" class="btn btn-primary btn-block">
                Back to Store Catalog
            </a>
            <button type="button" class="btn btn-secondary" onclick="showToast('Tax invoice saved to demo log.', 'info')">
                Print GST Receipt
            </button>
        </div>
    </div>
</dialog>
@endsection

@section('scripts')
<script>
let extraShippingCost = 0;
let selectedPaymentType = 'upi';

function renderCheckoutSummary() {
    const listEl = document.getElementById('checkoutItemsList');
    if (!listEl) return;

    const cart = getCart();
    if (cart.length === 0) {
        listEl.innerHTML = `
            <div style="text-align:center; padding:16px; color:var(--text-dim); font-size:0.85rem;">
                No items in cart. <a href="{{ route('catalog') }}" style="color:#818cf8;">Add some products first</a>
            </div>
        `;
    } else {
        listEl.innerHTML = cart.map(item => `
            <div style="display:flex; align-items:center; gap:12px;">
                <img src="${item.image}" alt="${escapeHtml(item.name)}" style="width:44px; height:44px; border-radius:8px; object-fit:cover; background:#0e1422;" onerror="this.src='/images/products/headphones.jpg'">
                <div style="flex:1; min-width:0;">
                    <div style="font-size:0.85rem; font-weight:600; color:var(--text-main); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${escapeHtml(item.name)}</div>
                    <div style="font-size:0.75rem; color:var(--text-dim);">Qty: ${item.quantity} | ${formatINR(item.price)}</div>
                </div>
                <div style="font-size:0.875rem; font-weight:700; color:var(--text-main);">
                    ${formatINR(parseFloat(item.price) * item.quantity)}
                </div>
            </div>
        `).join('');
    }

    recalcCheckout();
}

function handleShippingSpeedChange(cost, label) {
    extraShippingCost = cost;
    recalcCheckout();
}

function recalcCheckout() {
    const cart = getCart();
    const subtotal = cart.reduce((sum, item) => sum + (parseFloat(item.price) * (item.quantity || 1)), 0);

    let savedSummary = null;
    try {
        savedSummary = JSON.parse(localStorage.getItem('demo_cart_summary'));
    } catch(e) {}

    let discount = savedSummary ? (savedSummary.discount || 0) : 0;
    let baseShipping = (subtotal > 2000 || (savedSummary && savedSummary.promo === 'FREESHIP')) ? 0 : 149.00;
    let totalShipping = baseShipping + extraShippingCost;

    let taxable = Math.max(0, subtotal - discount);
    let tax = taxable * 0.18; // 18% GST
    let grandTotal = taxable + totalShipping + tax;

    document.getElementById('chkSubtotal').textContent = formatINR(subtotal);
    
    const discRow = document.getElementById('chkDiscountRow');
    if (discount > 0) {
        discRow.style.display = 'flex';
        document.getElementById('chkDiscount').textContent = `-${formatINR(discount)}`;
    } else {
        discRow.style.display = 'none';
    }

    document.getElementById('chkShipping').textContent = totalShipping === 0 ? 'FREE' : formatINR(totalShipping);
    document.getElementById('chkTax').textContent = formatINR(tax);
    document.getElementById('chkTotal').textContent = formatINR(grandTotal);
}

function switchPaymentMethod(method, tabEl) {
    selectedPaymentType = method;
    document.querySelectorAll('.payment-tab').forEach(t => t.classList.remove('active'));
    tabEl.classList.add('active');

    document.getElementById('upiPaymentBox').style.display = method === 'upi' ? 'block' : 'none';
    document.getElementById('cardPaymentBox').style.display = method === 'card' ? 'block' : 'none';
    document.getElementById('codBox').style.display = method === 'cod' ? 'block' : 'none';
}

function quickFillShipping() {
    document.getElementById('shipName').value = 'Alex Vance';
    document.getElementById('shipEmail').value = 'alex.vance@demo.in';
    document.getElementById('shipAddress').value = 'Flat 402, Lotus Heights, Bandra West';
    document.getElementById('shipCity').value = 'Mumbai';
    document.getElementById('shipPostal').value = 'Maharashtra - 400050';
    document.getElementById('shipPhone').value = '+91 98201 92834';

    showToast('Indian shipping address pre-filled.', 'info');
}

function quickFillPayment() {
    if (selectedPaymentType === 'upi') {
        document.getElementById('upiId').value = 'alex.vance@okaxis';
        showToast('Demo UPI ID pre-filled.', 'info');
    } else if (selectedPaymentType === 'card') {
        document.getElementById('cardNumber').value = '6074 8920 1192 8472';
        document.getElementById('cardExpiry').value = '08/29';
        document.getElementById('cardCvv').value = '742';
        document.getElementById('cardHolder').value = 'Alex Vance';
        showToast('Demo RuPay card pre-filled.', 'info');
    } else {
        showToast('Cash on Delivery selected.', 'info');
    }
}

function handlePlaceOrder() {
    const cart = getCart();
    if (cart.length === 0) {
        showToast('Your cart is empty. Add an item before checking out.', 'warning');
        return;
    }

    // Auto-fill defaults if user didn't click quick-fill
    if (!document.getElementById('shipName').value.trim()) {
        quickFillShipping();
    }

    const btn = document.getElementById('placeOrderBtn');
    btn.disabled = true;
    btn.innerHTML = `<span style="display:inline-block; width:14px; height:14px; border:2px solid rgba(255,255,255,0.3); border-top-color:#fff; border-radius:50%; animation:spin 0.8s linear infinite; vertical-align:middle; margin-right:8px;"></span> Processing Secure Payment...`;

    setTimeout(() => {
        // Prepare confirmation modal
        const randomOrderNum = '#AURA-IN' + Math.floor(10000 + Math.random() * 90000);
        document.getElementById('confirmedOrderNum').textContent = randomOrderNum;
        const totalText = document.getElementById('chkTotal').textContent;
        document.getElementById('confirmedTotal').textContent = totalText;
        
        let paymentDesc = 'UPI (alex.vance@okaxis)';
        if (selectedPaymentType === 'card') paymentDesc = 'RuPay Card ending in 8472';
        if (selectedPaymentType === 'cod') paymentDesc = 'Cash on Delivery (₹)';
        document.getElementById('confirmedPaymentMethod').textContent = paymentDesc;

        // Save order to demo_orders so Admin can inspect it on Orders page
        const user = getCurrentUser();
        const shipName = document.getElementById('shipName').value.trim() || (user ? user.name : 'Ananya Roy (Client)');
        const shipEmail = document.getElementById('shipEmail').value.trim() || (user ? user.email : 'client@aura.store');
        const shipPhone = document.getElementById('shipPhone').value.trim() || '+91 98201 92834';
        const shipAddr = document.getElementById('shipAddress').value.trim() || 'Flat 402, Lotus Heights, Bandra West';
        const shipCity = document.getElementById('shipCity').value.trim() || 'Mumbai';
        const shipPostal = document.getElementById('shipPostal').value.trim() || 'Maharashtra - 400050';
        
        const rawTotal = parseFloat(totalText.replace(/[^0-9.]/g, '')) || 0;

        const placedOrder = {
            id: randomOrderNum,
            customerName: shipName,
            customerEmail: shipEmail,
            customerPhone: shipPhone,
            address: `${shipAddr}, ${shipCity}, ${shipPostal}`,
            items: [...cart],
            total: rawTotal,
            paymentMethod: paymentDesc,
            status: 'Confirmed',
            date: new Date().toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) + ', ' + new Date().toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' })
        };
        addOrder(placedOrder);

        // Clear cart
        saveCart([]);

        btn.disabled = false;
        btn.innerHTML = `Order Placed!`;

        // Open celebration modal
        const dialog = document.getElementById('orderSuccessModal');
        if (dialog) dialog.showModal();

    }, 1100);
}
</script>
<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
@endsection
