@extends('layouts.app')

@section('title', 'Admin Orders Management & Fulfillment - Aura Store')

@section('content')
<div class="container">
    <!-- Admin Portal Header -->
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; margin-bottom:28px;">
        <div>
            <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(245, 158, 11, 0.15); border:1px solid rgba(245, 158, 11, 0.35); color:#d97706; padding:4px 12px; border-radius:var(--radius-full); font-size:0.8rem; font-weight:700; text-transform:uppercase; margin-bottom:8px;">
                Store Administrator Portal
            </div>
            <h1 style="font-size:2.2rem; font-weight:800; margin:0 0 6px;">Customer Orders &amp; Fulfillment</h1>
            <p style="color:var(--text-muted); margin:0;">Track customer checkouts, inspect payment methods, and update order fulfillment in Indian Rupees (₹ INR).</p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <button onclick="openAddProductModal()" class="btn btn-primary" style="background:linear-gradient(135deg, #f59e0b, #d97706);">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                Add Product
            </button>
            <a href="{{ route('catalog') }}" class="btn btn-secondary">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                View Catalog
            </a>
            <button onclick="resetDemoOrders()" class="btn btn-secondary" title="Restore default sample orders">
                Reset Sample Orders
            </button>
        </div>
    </div>

    <!-- Admin KPI Metrics Overview -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:18px; margin-bottom:28px;">
        <!-- KPI 1: Total Orders -->
        <div class="glass-card" style="padding:20px; display:flex; align-items:center; gap:16px;">
            <div style="width:48px; height:48px; border-radius:12px; background:rgba(99, 102, 241, 0.15); color:var(--primary); display:flex; align-items:center; justify-content:center;">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div>
                <span style="font-size:0.8rem; color:var(--text-dim); text-transform:uppercase; font-weight:700;">Total Orders</span>
                <div id="kpiTotalOrders" style="font-size:1.75rem; font-weight:800; color:var(--text-main);">0</div>
            </div>
        </div>

        <!-- KPI 2: Total Revenue -->
        <div class="glass-card" style="padding:20px; display:flex; align-items:center; gap:16px;">
            <div style="width:48px; height:48px; border-radius:12px; background:rgba(16, 185, 129, 0.15); color:#10b981; display:flex; align-items:center; justify-content:center; font-size:1.4rem; font-weight:800;">
                ₹
            </div>
            <div>
                <span style="font-size:0.8rem; color:var(--text-dim); text-transform:uppercase; font-weight:700;">Total Gross Revenue</span>
                <div id="kpiTotalRevenue" style="font-size:1.75rem; font-weight:800; color:#10b981;">₹0.00</div>
            </div>
        </div>

        <!-- KPI 3: Processing / Pending -->
        <div class="glass-card" style="padding:20px; display:flex; align-items:center; gap:16px;">
            <div style="width:48px; height:48px; border-radius:12px; background:rgba(245, 158, 11, 0.15); color:#d97706; display:flex; align-items:center; justify-content:center;">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/></svg>
            </div>
            <div>
                <span style="font-size:0.8rem; color:var(--text-dim); text-transform:uppercase; font-weight:700;">Processing / Dispatch</span>
                <div id="kpiProcessingOrders" style="font-size:1.75rem; font-weight:800; color:#d97706;">0</div>
            </div>
        </div>

        <!-- KPI 4: Delivered -->
        <div class="glass-card" style="padding:20px; display:flex; align-items:center; gap:16px;">
            <div style="width:48px; height:48px; border-radius:12px; background:rgba(6, 182, 212, 0.15); color:#0284c7; display:flex; align-items:center; justify-content:center;">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
                <span style="font-size:0.8rem; color:var(--text-dim); text-transform:uppercase; font-weight:700;">Delivered</span>
                <div id="kpiDeliveredOrders" style="font-size:1.75rem; font-weight:800; color:#0284c7;">0</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="store-toolbar" style="margin-bottom:24px;">
        <div class="search-box" style="flex:1; max-width:420px;">
            <svg class="search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input type="text" id="orderSearchInput" class="search-input" placeholder="Search by Order ID, customer, email, or city..." oninput="handleOrderSearch(this.value)">
        </div>

        <div class="filter-pills" id="orderFilterPills">
            <button class="filter-pill active" onclick="setOrderStatusFilter('All', this)">All Orders</button>
            <button class="filter-pill" onclick="setOrderStatusFilter('Confirmed', this)">Confirmed</button>
            <button class="filter-pill" onclick="setOrderStatusFilter('Processing', this)">Processing</button>
            <button class="filter-pill" onclick="setOrderStatusFilter('Shipped', this)">Shipped</button>
            <button class="filter-pill" onclick="setOrderStatusFilter('Delivered', this)">Delivered</button>
        </div>
    </div>

    <!-- Orders Container / Cards List -->
    <div id="adminOrdersTable" style="display:flex; flex-direction:column; gap:20px;">
        <!-- Rendered dynamically by demo.js / inline scripts -->
    </div>
</div>

<!-- Invoice / Order Detail Dialog -->
<dialog id="adminInvoiceModal" closedby="any" aria-labelledby="invoiceModalTitle">
    <div class="dialog-content" style="max-width: 620px;">
        <div class="dialog-header">
            <div>
                <h2 id="invoiceModalTitle" class="dialog-title">Order Tax Invoice &amp; Manifest</h2>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top:2px;">Government GST &amp; Dispatch Details</p>
            </div>
            <button type="button" class="dialog-close-btn" onclick="closeInvoiceModal()" aria-label="Close dialog">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div id="invoiceModalBody">
            <!-- Populated on click -->
        </div>

        <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
            <button type="button" class="btn btn-secondary" onclick="closeInvoiceModal()">Close</button>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                Print Invoice
            </button>
        </div>
    </div>
</dialog>

<!-- Add Product Modal Dialog (Also available directly on Admin Orders page) -->
<dialog id="addProductModal" closedby="any" aria-labelledby="modalTitle">
    <div class="dialog-content">
        <div class="dialog-header">
            <div>
                <h2 id="modalTitle" class="dialog-title">Add New Product to Catalog</h2>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top:2px;">Quickly publish items into the demo storefront</p>
            </div>
            <button type="button" class="dialog-close-btn" onclick="closeAddProductModal()" aria-label="Close dialog">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="addProductForm" onsubmit="handleAdminCreateProduct(event)">
            <div class="form-group">
                <label for="prodName" class="form-label">Product Name *</label>
                <input type="text" id="prodName" class="form-control" placeholder="e.g. Horizon Wireless Soundbar" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="prodCategory" class="form-label">Category *</label>
                    <select id="prodCategory" class="form-control" required>
                        <option value="Audio">Audio</option>
                        <option value="Wearables">Wearables</option>
                        <option value="Electronics" selected>Electronics</option>
                        <option value="Lifestyle">Lifestyle</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="prodPrice" class="form-label">Price (₹ INR) *</label>
                    <input type="number" id="prodPrice" class="form-control" placeholder="14999.00" step="1" min="1" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Choose Preset Thumbnail or Custom URL</label>
                <div style="display:flex; gap:10px; margin-bottom:10px; flex-wrap:wrap;">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="selectImagePreset('/images/products/headphones.jpg')">Headphones</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="selectImagePreset('/images/products/smartwatch.jpg')">Smartwatch</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="selectImagePreset('/images/products/keyboard.jpg')">Keyboard</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="selectImagePreset('/images/products/backpack.jpg')">Backpack</button>
                </div>
                <input type="text" id="prodImage" class="form-control" placeholder="/images/... or https://..." value="/images/products/keyboard.jpg" required>
            </div>

            <div class="form-group">
                <label for="prodBadge" class="form-label">Badge Tag (Optional)</label>
                <input type="text" id="prodBadge" class="form-control" placeholder="e.g. Admin Featured, Top Pick">
            </div>

            <div class="form-group">
                <label for="prodDesc" class="form-label">Product Description</label>
                <textarea id="prodDesc" class="form-control" rows="3" placeholder="Briefly describe highlights and key specifications..."></textarea>
            </div>

            <div style="display:flex; gap:12px; justify-content:flex-end; margin-top:24px;">
                <button type="button" class="btn btn-secondary" onclick="closeAddProductModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #f59e0b, #d97706);">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Publish to Storefront
                </button>
            </div>
        </form>
    </div>
</dialog>
@endsection

@section('scripts')
<script>
let currentStatusFilter = 'All';
let currentOrderSearch = '';

function renderAdminOrdersPage() {
    const container = document.getElementById('adminOrdersTable');
    if (!container) return;

    let orders = getOrders();

    // Calculate KPI figures
    const totalOrders = orders.length;
    const totalRevenue = orders.reduce((sum, o) => sum + (Number(o.total) || 0), 0);
    const processingCount = orders.filter(o => o.status === 'Processing' || o.status === 'Confirmed' || o.status === 'Shipped').length;
    const deliveredCount = orders.filter(o => o.status === 'Delivered').length;

    document.getElementById('kpiTotalOrders').textContent = totalOrders;
    document.getElementById('kpiTotalRevenue').textContent = formatINR(totalRevenue);
    document.getElementById('kpiProcessingOrders').textContent = processingCount;
    document.getElementById('kpiDeliveredOrders').textContent = deliveredCount;

    // Filter by Status
    if (currentStatusFilter !== 'All') {
        orders = orders.filter(o => o.status.toLowerCase() === currentStatusFilter.toLowerCase());
    }

    // Filter by Search Query
    if (currentOrderSearch.trim()) {
        const q = currentOrderSearch.toLowerCase();
        orders = orders.filter(o => 
            o.id.toLowerCase().includes(q) ||
            o.customerName.toLowerCase().includes(q) ||
            (o.customerEmail && o.customerEmail.toLowerCase().includes(q)) ||
            (o.address && o.address.toLowerCase().includes(q))
        );
    }

    if (orders.length === 0) {
        container.innerHTML = `
            <div class="empty-state" style="padding:48px 24px;">
                <div class="empty-icon">
                    <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-4a2 2 0 01-2-2v-1a2 2 0 00-2-2H8a2 2 0 00-2 2v1a2 2 0 01-2 2H4"></path></svg>
                </div>
                <h3 style="font-size:1.3rem; margin-bottom:8px;">No customer orders found</h3>
                <p style="margin-bottom:20px; color:var(--text-muted);">
                    ${currentStatusFilter !== 'All' || currentOrderSearch ? 'No orders match your filter criteria.' : 'No orders have been submitted yet.'}
                </p>
                <div style="display:flex; gap:12px; justify-content:center;">
                    <a href="{{ route('catalog') }}" class="btn btn-primary">Go to Catalog</a>
                    <button onclick="resetDemoOrders()" class="btn btn-secondary">Restore Sample Orders</button>
                </div>
            </div>
        `;
        return;
    }

    container.innerHTML = orders.map(order => {
        let statusBadgeStyle = 'background:rgba(99, 102, 241, 0.15); color:var(--primary); border:1px solid rgba(99, 102, 241, 0.3);';
        if (order.status === 'Confirmed') {
            statusBadgeStyle = 'background:rgba(245, 158, 11, 0.15); color:#d97706; border:1px solid rgba(245, 158, 11, 0.3);';
        } else if (order.status === 'Processing') {
            statusBadgeStyle = 'background:rgba(2, 132, 199, 0.15); color:#0284c7; border:1px solid rgba(2, 132, 199, 0.3);';
        } else if (order.status === 'Shipped') {
            statusBadgeStyle = 'background:rgba(139, 92, 246, 0.15); color:#8b5cf6; border:1px solid rgba(139, 92, 246, 0.3);';
        } else if (order.status === 'Delivered') {
            statusBadgeStyle = 'background:rgba(16, 185, 129, 0.15); color:#10b981; border:1px solid rgba(16, 185, 129, 0.3);';
        }

        const itemsHtml = (order.items || []).map(item => `
            <div style="display:flex; align-items:center; gap:12px; padding:8px 0; border-bottom:1px dashed var(--border-subtle);">
                <img src="${item.image || '/images/products/headphones.jpg'}" alt="${escapeHtml(item.name)}" style="width:40px; height:40px; border-radius:8px; object-fit:cover; background:var(--bg-input);">
                <div style="flex:1;">
                    <div style="font-weight:600; font-size:0.9rem; color:var(--text-main);">${escapeHtml(item.name)}</div>
                    <div style="font-size:0.75rem; color:var(--text-dim);">${escapeHtml(item.category || 'Product')} | Qty: ${item.quantity || 1}</div>
                </div>
                <div style="font-weight:700; font-size:0.9rem; color:var(--text-main);">
                    ${formatINR((Number(item.price) || 0) * (item.quantity || 1))}
                </div>
            </div>
        `).join('');

        return `
            <div class="glass-card" style="padding:24px; border-radius:var(--radius-lg); position:relative;" id="order-card-${order.id}">
                <!-- Order Top Bar -->
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:14px; margin-bottom:16px; padding-bottom:16px; border-bottom:1px solid var(--border-subtle);">
                    <div>
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
                            <span style="font-family:'Space Grotesk', sans-serif; font-weight:800; font-size:1.15rem; color:var(--text-main);">${escapeHtml(order.id)}</span>
                            <span style="padding:3px 10px; border-radius:var(--radius-full); font-size:0.75rem; font-weight:700; ${statusBadgeStyle}">
                                ${escapeHtml(order.status)}
                            </span>
                        </div>
                        <span style="font-size:0.8rem; color:var(--text-dim);">Placed on: ${escapeHtml(order.date || 'Recent')}</span>
                    </div>

                    <!-- Status Selector for Admin -->
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="font-size:0.8rem; font-weight:600; color:var(--text-muted);">Update Status:</span>
                        <select onchange="handleAdminStatusChange('${order.id}', this.value)" class="form-control" style="padding:6px 12px; font-size:0.85rem; width:auto; border-radius:var(--radius-sm); font-weight:600;">
                            <option value="Confirmed" ${order.status === 'Confirmed' ? 'selected' : ''}>Confirmed</option>
                            <option value="Processing" ${order.status === 'Processing' ? 'selected' : ''}>Processing</option>
                            <option value="Shipped" ${order.status === 'Shipped' ? 'selected' : ''}>Shipped</option>
                            <option value="Delivered" ${order.status === 'Delivered' ? 'selected' : ''}>Delivered</option>
                        </select>
                    </div>
                </div>

                <!-- Order Content Grid -->
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:20px; margin-bottom:18px;">
                    <!-- Customer Information -->
                    <div style="background:var(--bg-input); padding:16px; border-radius:var(--radius-md); border:1px solid var(--border-subtle);">
                        <span style="font-size:0.75rem; text-transform:uppercase; font-weight:700; color:var(--text-dim); display:block; margin-bottom:8px;">Customer &amp; Shipping</span>
                        <div style="font-weight:700; color:var(--text-main); font-size:0.95rem; margin-bottom:2px;">${escapeHtml(order.customerName)}</div>
                        <div style="font-size:0.82rem; color:var(--text-muted); margin-bottom:4px;">Email: ${escapeHtml(order.customerEmail || 'client@aura.store')}</div>
                        <div style="font-size:0.82rem; color:var(--text-muted); margin-bottom:6px;">Phone: ${escapeHtml(order.customerPhone || '+91 98201 44321')}</div>
                        <div style="font-size:0.8rem; color:var(--text-dim); line-height:1.4;">Address: ${escapeHtml(order.address || 'Standard Delivery Address')}</div>
                    </div>

                    <!-- Payment Information -->
                    <div style="background:var(--bg-input); padding:16px; border-radius:var(--radius-md); border:1px solid var(--border-subtle);">
                        <span style="font-size:0.75rem; text-transform:uppercase; font-weight:700; color:var(--text-dim); display:block; margin-bottom:8px;">Payment &amp; Currency</span>
                        <div style="font-weight:700; color:var(--text-main); font-size:0.95rem; margin-bottom:2px;">${escapeHtml(order.paymentMethod || 'UPI Payment')}</div>
                        <div style="font-size:0.82rem; color:#10b981; font-weight:600; margin-bottom:6px;">Payment Verified &amp; Captured</div>
                        <div style="font-size:0.8rem; color:var(--text-muted);">Currency: <strong style="color:var(--text-main);">Indian Rupee (₹ INR)</strong></div>
                        <div style="font-size:0.8rem; color:var(--text-dim); margin-top:4px;">GST Invoice: Included (18% Integrated Tax)</div>
                    </div>
                </div>

                <!-- Purchased Items List -->
                <div style="margin-bottom:16px;">
                    <span style="font-size:0.75rem; text-transform:uppercase; font-weight:700; color:var(--text-dim); display:block; margin-bottom:6px;">Purchased Items (${(order.items || []).length})</span>
                    <div style="max-height:160px; overflow-y:auto;">
                        ${itemsHtml}
                    </div>
                </div>

                <!-- Footer Summary -->
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; padding-top:14px; border-top:1px solid var(--border-subtle);">
                    <div>
                        <span style="font-size:0.8rem; color:var(--text-dim);">Total Amount (Incl. GST):</span>
                        <div style="font-size:1.35rem; font-weight:800; color:#10b981;">${formatINR(order.total)}</div>
                    </div>

                    <div style="display:flex; gap:10px;">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="openInvoiceModal('${order.id}')">
                            View Invoice
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function handleOrderSearch(val) {
    currentOrderSearch = val;
    renderAdminOrdersPage();
}

function setOrderStatusFilter(status, buttonEl) {
    currentStatusFilter = status;
    document.querySelectorAll('#orderFilterPills .filter-pill').forEach(btn => btn.classList.remove('active'));
    if (buttonEl) buttonEl.classList.add('active');
    renderAdminOrdersPage();
}

function handleAdminStatusChange(orderId, newStatus) {
    updateOrderStatus(orderId, newStatus);
    renderAdminOrdersPage();
}

function resetDemoOrders() {
    localStorage.setItem('demo_orders', JSON.stringify(DEFAULT_ORDERS));
    showToast('Demo customer orders reset to initial state!', 'info');
    renderAdminOrdersPage();
}

function openInvoiceModal(orderId) {
    const orders = getOrders();
    const order = orders.find(o => o.id === orderId);
    if (!order) return;

    const modalBody = document.getElementById('invoiceModalBody');
    const subtotal = order.total / 1.18;
    const gst = order.total - subtotal;

    modalBody.innerHTML = `
        <div style="background:var(--bg-input); padding:20px; border-radius:var(--radius-md); border:1px solid var(--border-subtle); margin-bottom:16px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:12px; border-bottom:1px solid var(--border-subtle); padding-bottom:12px;">
                <div>
                    <h3 style="margin:0; font-size:1.1rem; color:var(--text-main);">Aura Store India Pvt Ltd</h3>
                    <span style="font-size:0.75rem; color:var(--text-dim);">GSTIN: 27AABCA1234F1Z5</span>
                </div>
                <div style="text-align:right;">
                    <div style="font-weight:700; color:var(--primary); font-family:'Space Grotesk',sans-serif;">${escapeHtml(order.id)}</div>
                    <span style="font-size:0.75rem; color:var(--text-dim);">${escapeHtml(order.date || 'Today')}</span>
                </div>
            </div>

            <div style="font-size:0.85rem; margin-bottom:14px; line-height:1.5;">
                <strong style="display:block; color:var(--text-main);">Billed &amp; Shipped To:</strong>
                <div>${escapeHtml(order.customerName)}</div>
                <div style="color:var(--text-muted);">${escapeHtml(order.customerEmail)} | ${escapeHtml(order.customerPhone)}</div>
                <div style="color:var(--text-dim);">${escapeHtml(order.address)}</div>
            </div>

            <div style="font-size:0.85rem; border-top:1px solid var(--border-subtle); padding-top:12px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                    <span style="color:var(--text-muted);">Taxable Subtotal:</span>
                    <span style="font-weight:600;">${formatINR(subtotal)}</span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                    <span style="color:var(--text-muted);">GST (18% IGST / CGST+SGST):</span>
                    <span style="font-weight:600;">${formatINR(gst)}</span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                    <span style="color:var(--text-muted);">Payment Method:</span>
                    <span style="font-weight:600; color:#10b981;">${escapeHtml(order.paymentMethod)}</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:1.1rem; font-weight:800; border-top:1px solid var(--border-subtle); padding-top:8px;">
                    <span style="color:var(--text-main);">Grand Total Paid:</span>
                    <span style="color:#10b981;">${formatINR(order.total)}</span>
                </div>
            </div>
        </div>
    `;

    const dialog = document.getElementById('adminInvoiceModal');
    if (dialog) dialog.showModal();
}

function closeInvoiceModal() {
    const dialog = document.getElementById('adminInvoiceModal');
    if (dialog) dialog.close();
}

function openAddProductModal() {
    const dialog = document.getElementById('addProductModal');
    if (dialog) dialog.showModal();
}

function closeAddProductModal() {
    const dialog = document.getElementById('addProductModal');
    if (dialog) dialog.close();
}

function selectImagePreset(url) {
    const input = document.getElementById('prodImage');
    if (input) input.value = url;
}

function handleAdminCreateProduct(e) {
    e.preventDefault();
    const name = document.getElementById('prodName').value.trim();
    const category = document.getElementById('prodCategory').value;
    const price = parseFloat(document.getElementById('prodPrice').value);
    const image = document.getElementById('prodImage').value.trim() || '/images/products/headphones.jpg';
    const badge = document.getElementById('prodBadge').value.trim();
    const description = document.getElementById('prodDesc').value.trim();

    if (!name || isNaN(price)) {
        showToast('Please provide valid product details.', 'warning');
        return;
    }

    const newProd = {
        id: 'prod-' + Date.now(),
        name,
        category,
        price,
        image,
        badge: badge || 'Admin Added',
        description: description || 'New product added via admin portal.'
    };

    const products = getProducts();
    products.unshift(newProd);
    saveProducts(products);

    closeAddProductModal();
    document.getElementById('addProductForm').reset();
    showToast(`Successfully added <strong>${escapeHtml(newProd.name)}</strong> to catalog!`, 'success');
}

document.addEventListener('DOMContentLoaded', () => {
    renderAdminOrdersPage();
});
</script>
@endsection
