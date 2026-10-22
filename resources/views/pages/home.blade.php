@extends('layouts.app')

@section('title', 'Aura Store - Discover Premium Tech & Lifestyle')

@section('content')
<div class="container">
    <!-- Admin Controls Notification Banner (Visible to Admin only) -->
    <div id="adminNoticeBar" style="display:none; background:linear-gradient(135deg, rgba(245, 158, 11, 0.12), rgba(239, 68, 68, 0.08)); border:1px solid rgba(245, 158, 11, 0.35); border-radius:var(--radius-md); padding:14px 20px; margin-bottom:24px; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:36px; height:36px; border-radius:8px; background:linear-gradient(135deg, #f59e0b, #d97706); color:#fff; display:flex; align-items:center; justify-content:center;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
            <div>
                <strong style="color:var(--text-main); font-size:0.95rem; display:block;">Administrator Portal Active</strong>
                <span style="color:var(--text-muted); font-size:0.82rem;">You have administrative permissions to add new products to the catalog and inspect customer orders.</span>
            </div>
        </div>
        <div style="display:flex; gap:10px;">
            <button onclick="openAddProductModal()" class="btn btn-sm btn-primary" style="background:linear-gradient(135deg, #f59e0b, #d97706);">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                Add Product
            </button>
            <a href="{{ route('admin.orders') }}" class="btn btn-sm btn-secondary" style="border-color:rgba(245,158,11,0.5); font-weight:700; color:var(--text-main);">
                Check Orders (<span id="adminOrdersCountBadge">2</span>)
            </a>
        </div>
    </div>

    <!-- Hero Banner -->
    <section class="hero">
        <div class="hero-badge">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
            2026 Curated Tech Collection
        </div>
        <h1 class="hero-title">Elevate Your Everyday with <span>Aura Design</span></h1>
        <p class="hero-subtitle">Explore cutting-edge acoustics, wearable innovation, and workspace gear engineered for modern creators.</p>
        
        <div class="hero-actions">
            <!-- Admin Buttons -->
            <button onclick="openAddProductModal()" class="btn btn-primary btn-lg" id="openAddModalBtn" style="background:linear-gradient(135deg, #f59e0b, #d97706); display:none;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                Add New Product
            </button>
            <a href="{{ route('admin.orders') }}" class="btn btn-secondary btn-lg" id="heroAdminOrdersBtn" style="display:none;">
                Check Customer Orders
            </a>

            <!-- Client Buttons -->
            <a href="#productsGrid" class="btn btn-primary btn-lg" id="heroBrowseBtn">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                Browse Catalog
            </a>
            <a href="{{ route('cart') }}" class="btn btn-secondary btn-lg" id="heroCartBtn">
                View My Cart
            </a>
        </div>
    </section>

    <!-- Toolbar: Search, Filters, and Quick Add Action -->
    <div class="store-toolbar">
        <div class="search-box">
            <svg class="search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input type="text" id="searchInput" class="search-input" placeholder="Search products by title or category..." oninput="handleSearch(this.value)">
        </div>

        <div class="filter-pills" id="filterPills">
            <button class="filter-pill active" onclick="setCategoryFilter('All', this)">All Items</button>
            <button class="filter-pill" onclick="setCategoryFilter('Audio', this)">Audio</button>
            <button class="filter-pill" onclick="setCategoryFilter('Wearables', this)">Wearables</button>
            <button class="filter-pill" onclick="setCategoryFilter('Electronics', this)">Electronics</button>
            <button class="filter-pill" onclick="setCategoryFilter('Lifestyle', this)">Lifestyle</button>
        </div>

        <!-- Add Item Button: Admin only -->
        <button onclick="openAddProductModal()" class="btn btn-sm btn-quickfill" id="toolbarAddBtn" style="margin-left:auto; display:none;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Add Product
        </button>
    </div>

    <!-- Products Grid -->
    <div class="products-grid" id="productsGrid" aria-live="polite">
        <!-- Rendered dynamically by demo.js -->
    </div>
</div>

<!-- Add Product Modal Dialog -->
<dialog id="addProductModal" closedby="any" aria-labelledby="modalTitle">
    <div class="dialog-content">
        <div class="dialog-header">
            <div>
                <h2 id="modalTitle" class="dialog-title">Add New Product</h2>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top:2px;">Populate your catalog instantly without a database</p>
            </div>
            <button type="button" class="dialog-close-btn" onclick="closeAddProductModal()" aria-label="Close dialog">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="addProductForm" onsubmit="handleCreateProduct(event)">
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
                <label class="form-label">Choose Product Image Preset or Custom URL</label>
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
                <input type="text" id="prodBadge" class="form-control" placeholder="e.g. Staff Pick, Trending, 20% Off">
            </div>

            <div class="form-group">
                <label for="prodDesc" class="form-label">Product Description</label>
                <textarea id="prodDesc" class="form-control" rows="3" placeholder="Briefly describe highlights, specs, and materials..."></textarea>
            </div>

            <div style="display:flex; gap:12px; justify-content:flex-end; margin-top:24px;">
                <button type="button" class="btn btn-secondary" onclick="closeAddProductModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Product to Catalog
                </button>
            </div>
        </form>
    </div>
</dialog>
@endsection

@section('scripts')
<script>
let currentCategory = 'All';
let currentSearch = '';

function renderProductsGrid() {
    const grid = document.getElementById('productsGrid');
    if (!grid) return;

    let products = getProducts();

    // Filter by Category
    if (currentCategory !== 'All') {
        products = products.filter(p => p.category.toLowerCase() === currentCategory.toLowerCase());
    }

    // Filter by Search
    if (currentSearch.trim()) {
        const q = currentSearch.toLowerCase();
        products = products.filter(p => 
            p.name.toLowerCase().includes(q) || 
            p.category.toLowerCase().includes(q) ||
            (p.description && p.description.toLowerCase().includes(q))
        );
    }

    if (products.length === 0) {
        grid.innerHTML = `
            <div style="grid-column: 1 / -1;" class="empty-state">
                <div class="empty-icon">
                    <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <h3 style="font-size:1.3rem; margin-bottom:8px;">No matching products found</h3>
                <p style="margin-bottom:20px;">Try adjusting your search query or category filters, or add a new product.</p>
                <button onclick="openAddProductModal()" class="btn btn-primary">Add New Product</button>
            </div>
        `;
        return;
    }

    grid.innerHTML = products.map(prod => `
        <article class="product-card" id="card-${prod.id}">
            <div class="product-img-wrap">
                <img src="${prod.image}" alt="${escapeHtml(prod.name)}" class="product-img" loading="lazy" onerror="this.src='/images/products/headphones.jpg'">
                ${prod.badge ? `<span class="product-badge">${escapeHtml(prod.badge)}</span>` : ''}
            </div>
            <div class="product-info">
                <span class="product-category">${escapeHtml(prod.category)}</span>
                <h3 class="product-title">${escapeHtml(prod.name)}</h3>
                <p class="product-desc">${escapeHtml(prod.description || 'Premium design with industry leading durability and performance.')}</p>
                <div class="product-footer">
                    <div class="product-price">
                        <span>₹</span>${Number(prod.price).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                    </div>
                    <button onclick="addToCart('${prod.id}')" class="btn btn-primary btn-sm" aria-label="Add ${escapeHtml(prod.name)} to cart">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Add to Cart
                    </button>
                </div>
            </div>
        </article>
    `).join('');
}

function handleSearch(val) {
    currentSearch = val;
    renderProductsGrid();
}

function setCategoryFilter(category, buttonEl) {
    currentCategory = category;
    document.querySelectorAll('.filter-pill').forEach(btn => btn.classList.remove('active'));
    if (buttonEl) buttonEl.classList.add('active');
    renderProductsGrid();
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

function handleCreateProduct(e) {
    e.preventDefault();
    const name = document.getElementById('prodName').value.trim();
    const category = document.getElementById('prodCategory').value;
    const price = parseFloat(document.getElementById('prodPrice').value);
    const image = document.getElementById('prodImage').value.trim() || '/images/products/headphones.jpg';
    const badge = document.getElementById('prodBadge').value.trim();
    const description = document.getElementById('prodDesc').value.trim();

    if (!name || isNaN(price)) {
        showToast('Please fill out product name and valid price.', 'warning');
        return;
    }

    const newProd = {
        id: 'prod-' + Date.now(),
        name,
        category,
        price,
        image,
        badge: badge || 'New',
        description: description || 'Newly added catalog product.'
    };

    const products = getProducts();
    products.unshift(newProd);
    saveProducts(products);

    closeAddProductModal();
    document.getElementById('addProductForm').reset();
    showToast(`Successfully added <strong>${escapeHtml(newProd.name)}</strong>!`, 'success');
    renderProductsGrid();
    updateRoleUI();
}

function updateRoleUI() {
    const admin = isAdmin();
    const noticeBar = document.getElementById('adminNoticeBar');
    const heroAddBtn = document.getElementById('openAddModalBtn');
    const heroOrdersBtn = document.getElementById('heroAdminOrdersBtn');
    const heroBrowseBtn = document.getElementById('heroBrowseBtn');
    const heroCartBtn = document.getElementById('heroCartBtn');
    const toolbarAddBtn = document.getElementById('toolbarAddBtn');
    const ordersBadge = document.getElementById('adminOrdersCountBadge');

    if (noticeBar) noticeBar.style.display = admin ? 'flex' : 'none';
    if (heroAddBtn) heroAddBtn.style.display = admin ? 'inline-flex' : 'none';
    if (heroOrdersBtn) heroOrdersBtn.style.display = admin ? 'inline-flex' : 'none';
    if (toolbarAddBtn) toolbarAddBtn.style.display = admin ? 'inline-flex' : 'none';
    
    // For clients:
    if (heroBrowseBtn) heroBrowseBtn.style.display = admin ? 'none' : 'inline-flex';
    if (heroCartBtn) heroCartBtn.style.display = admin ? 'none' : 'inline-flex';

    if (ordersBadge) {
        ordersBadge.textContent = getOrders().length;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    updateRoleUI();
});
</script>
@endsection
