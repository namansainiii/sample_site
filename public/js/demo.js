/**
 * Aura Demo Storefront - Dynamic State & Interactive Engine
 * Multi-Role: Admin (Catalog Management & Orders) & Client (Browsing, Cart & Checkout)
 * Formatted in Indian Rupee (₹ INR).
 */

// Currency & state migration check
if (typeof localStorage !== 'undefined' && localStorage.getItem('demo_currency') !== 'INR_v3') {
  localStorage.removeItem('demo_products');
  localStorage.removeItem('demo_cart');
  localStorage.removeItem('demo_cart_summary');
  localStorage.setItem('demo_currency', 'INR_v3');
}

const DEFAULT_PRODUCTS = [
  {
    id: 'prod-1',
    name: 'Aether ANC Wireless Headphones',
    category: 'Audio',
    price: 19999.00,
    badge: 'Best Seller',
    image: '/images/products/headphones.jpg',
    description: 'Precision engineered active noise-canceling headphones with 40mm acoustic drivers and 40h battery life.'
  },
  {
    id: 'prod-2',
    name: 'Vanguard Ultra Smartwatch',
    category: 'Wearables',
    price: 24999.00,
    badge: 'Popular',
    image: '/images/products/smartwatch.jpg',
    description: 'Aerospace-grade titanium chassis with vivid sapphire OLED display, ECG health sensors and GPS.'
  },
  {
    id: 'prod-3',
    name: 'Lumik Artisan Mechanical Keyboard',
    category: 'Electronics',
    price: 12499.00,
    badge: 'Featured',
    image: '/images/products/keyboard.jpg',
    description: 'Custom CNC aluminum body, hot-swappable tactile switches, sound dampening foam and warm ambient LED.'
  },
  {
    id: 'prod-4',
    name: 'Nomad Urban Commuter Backpack',
    category: 'Lifestyle',
    price: 3999.00,
    badge: 'New Arrival',
    image: '/images/products/backpack.jpg',
    description: 'Weatherproof ballistic nylon shell with magnetic Fidlock closures and dedicated padded 16" laptop pocket.'
  }
];

const DEFAULT_ORDERS = [
  {
    id: '#AURA-IN84920',
    customerName: 'Priya Sharma',
    customerEmail: 'priya.sharma@example.in',
    customerPhone: '+91 98201 44321',
    address: 'Flat 301, Marine Drive, Mumbai, Maharashtra - 400020',
    items: [
      { id: 'prod-1', name: 'Aether ANC Wireless Headphones', category: 'Audio', price: 19999.00, quantity: 1, image: '/images/products/headphones.jpg' }
    ],
    total: 23598.82,
    paymentMethod: 'UPI (priya@okhdfcbank)',
    status: 'Delivered',
    date: '21 Oct 2026, 02:45 PM'
  },
  {
    id: '#AURA-IN71024',
    customerName: 'Rahul Verma',
    customerEmail: 'rahul.verma@example.in',
    customerPhone: '+91 98765 12340',
    address: 'Tower B, Cyber City, Gurugram, Haryana - 122002',
    items: [
      { id: 'prod-2', name: 'Vanguard Ultra Smartwatch', category: 'Wearables', price: 24999.00, quantity: 1, image: '/images/products/smartwatch.jpg' },
      { id: 'prod-4', name: 'Nomad Urban Commuter Backpack', category: 'Lifestyle', price: 3999.00, quantity: 1, image: '/images/products/backpack.jpg' }
    ],
    total: 34217.64,
    paymentMethod: 'RuPay Card ending in 6074',
    status: 'Processing',
    date: '22 Oct 2026, 11:15 AM'
  }
];

// Helper: Format amount into Indian Rupee (₹)
function formatINR(amount) {
  const num = Number(amount) || 0;
  return '₹' + num.toLocaleString('en-IN', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
}

// Product Store
function getProducts() {
  try {
    const stored = localStorage.getItem('demo_products');
    if (!stored) {
      localStorage.setItem('demo_products', JSON.stringify(DEFAULT_PRODUCTS));
      return DEFAULT_PRODUCTS;
    }
    const parsed = JSON.parse(stored);
    return Array.isArray(parsed) && parsed.length > 0 ? parsed : DEFAULT_PRODUCTS;
  } catch (e) {
    return DEFAULT_PRODUCTS;
  }
}

function saveProducts(products) {
  localStorage.setItem('demo_products', JSON.stringify(products));
}

// Order Store (For Admin Orders Dashboard)
function getOrders() {
  try {
    const stored = localStorage.getItem('demo_orders');
    if (!stored) {
      localStorage.setItem('demo_orders', JSON.stringify(DEFAULT_ORDERS));
      return DEFAULT_ORDERS;
    }
    const parsed = JSON.parse(stored);
    return Array.isArray(parsed) ? parsed : DEFAULT_ORDERS;
  } catch (e) {
    return DEFAULT_ORDERS;
  }
}

function saveOrders(orders) {
  localStorage.setItem('demo_orders', JSON.stringify(orders));
}

function addOrder(order) {
  const orders = getOrders();
  orders.unshift(order);
  saveOrders(orders);
}

function updateOrderStatus(orderId, newStatus) {
  const orders = getOrders();
  const order = orders.find(o => o.id === orderId);
  if (order) {
    order.status = newStatus;
    saveOrders(orders);
    showToast(`Order <strong>${escapeHtml(orderId)}</strong> updated to <strong>${escapeHtml(newStatus)}</strong>`, 'success');
  }
}

// Cart Store
function getCart() {
  try {
    const stored = localStorage.getItem('demo_cart');
    return stored ? JSON.parse(stored) : [
      { id: 'prod-1', name: 'Aether ANC Wireless Headphones', category: 'Audio', price: 19999.00, quantity: 1, image: '/images/products/headphones.jpg' }
    ];
  } catch (e) {
    return [];
  }
}

function saveCart(cart) {
  localStorage.setItem('demo_cart', JSON.stringify(cart));
  updateCartBadge();
}

function addToCart(productId, qty = 1) {
  const user = getCurrentUser();
  if (user && user.role === 'admin') {
    showToast('Admin Mode: Switched to client shopping preview to add item.', 'info');
  }

  const products = getProducts();
  const product = products.find(p => p.id === productId);
  if (!product) return;

  const cart = getCart();
  const existing = cart.find(item => item.id === productId);
  if (existing) {
    existing.quantity += qty;
  } else {
    cart.push({
      id: product.id,
      name: product.name,
      category: product.category,
      price: product.price,
      image: product.image,
      quantity: qty
    });
  }

  saveCart(cart);
  showToast(`Added <strong>${escapeHtml(product.name)}</strong> to cart!`, 'success');
}

function updateCartQty(productId, delta) {
  const cart = getCart();
  const itemIndex = cart.findIndex(item => item.id === productId);
  if (itemIndex > -1) {
    cart[itemIndex].quantity += delta;
    if (cart[itemIndex].quantity <= 0) {
      cart.splice(itemIndex, 1);
      showToast('Item removed from cart.', 'warning');
    }
  }
  saveCart(cart);
  renderCartPage();
}

function removeFromCart(productId) {
  let cart = getCart();
  cart = cart.filter(item => item.id !== productId);
  saveCart(cart);
  showToast('Item removed from cart.', 'warning');
  renderCartPage();
}

function clearCart() {
  saveCart([]);
  showToast('Cart cleared.', 'warning');
  renderCartPage();
}

function updateCartBadge() {
  const cart = getCart();
  const count = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
  const badgeEl = document.getElementById('cartNavBadge');
  if (badgeEl) {
    badgeEl.textContent = count;
    badgeEl.classList.remove('bump');
    void badgeEl.offsetWidth; // trigger reflow
    badgeEl.classList.add('bump');
  }
}

// User Auth State & Roles
function getCurrentUser() {
  try {
    const user = localStorage.getItem('demo_user');
    return user ? JSON.parse(user) : null;
  } catch (e) {
    return null;
  }
}

function setCurrentUser(user) {
  if (user) {
    localStorage.setItem('demo_user', JSON.stringify(user));
    document.cookie = "demo_logged_in=" + encodeURIComponent(user.role) + "; path=/; max-age=86400; SameSite=Lax";
  } else {
    localStorage.removeItem('demo_user');
    document.cookie = "demo_logged_in=; path=/; max-age=0; SameSite=Lax";
  }
  renderUserNav();
}

function isAdmin() {
  const u = getCurrentUser();
  return u && u.role === 'admin';
}

function isClient() {
  const u = getCurrentUser();
  return u && u.role === 'client';
}

function renderUserNav() {
  const container = document.getElementById('userNavContainer');
  const cartBtn = document.getElementById('cartNavBtn');
  const navLinksContainer = document.getElementById('mainNavLinks');
  const brandLogo = document.getElementById('brandLogoLink');
  const footerNav = document.getElementById('footerNavLinks');
  const user = getCurrentUser();
  const path = window.location.pathname;
  const isAuthPage = path === '/login' || path === '/register';

  // Dynamic Navigation Links: Strictly lock down on login and register pages
  if (navLinksContainer) {
    if (isAuthPage || !user) {
      navLinksContainer.innerHTML = '';
    } else if (user.role === 'admin') {
      navLinksContainer.innerHTML = `
        <li>
          <a href="/catalog" class="nav-link ${path === '/catalog' ? 'active' : ''}">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            Catalog &amp; Products
          </a>
        </li>
        <li>
          <a href="/admin/orders" class="nav-link ${path.startsWith('/admin') ? 'active' : ''}" style="color:var(--primary); font-weight:700;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            Manage Orders
            <span style="background:var(--primary); color:#fff; font-size:0.7rem; padding:1px 6px; border-radius:10px; margin-left:4px;">${getOrders().length}</span>
          </a>
        </li>
      `;
    } else {
      // Client role
      navLinksContainer.innerHTML = `
        <li>
          <a href="/catalog" class="nav-link ${path === '/catalog' ? 'active' : ''}">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Browse Catalog
          </a>
        </li>
        <li>
          <a href="/cart" class="nav-link ${path === '/cart' ? 'active' : ''}">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            My Cart
          </a>
        </li>
        <li>
          <a href="/checkout" class="nav-link ${path === '/checkout' ? 'active' : ''}">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Checkout
          </a>
        </li>
      `;
    }
  }

  // Brand logo destination: always /login when on auth page or logged out
  if (brandLogo) {
    brandLogo.href = (isAuthPage || !user) ? '/login' : '/catalog';
  }

  // Footer links synchronization
  if (footerNav) {
    if (isAuthPage || !user) {
      footerNav.innerHTML = `
        <a href="/login" class="nav-link" style="padding:4px 8px; font-size:0.85rem;">Sign In</a>
        <a href="/register" class="nav-link" style="padding:4px 8px; font-size:0.85rem;">Register</a>
      `;
    } else if (user.role === 'admin') {
      footerNav.innerHTML = `
        <a href="/catalog" class="nav-link" style="padding:4px 8px; font-size:0.85rem;">Catalog &amp; Products</a>
        <a href="/admin/orders" class="nav-link" style="padding:4px 8px; font-size:0.85rem;">Manage Orders</a>
      `;
    } else {
      footerNav.innerHTML = `
        <a href="/catalog" class="nav-link" style="padding:4px 8px; font-size:0.85rem;">Catalog</a>
        <a href="/cart" class="nav-link" style="padding:4px 8px; font-size:0.85rem;">Cart</a>
        <a href="/checkout" class="nav-link" style="padding:4px 8px; font-size:0.85rem;">Checkout</a>
      `;
    }
  }

  // Cart button visibility: only for client when authenticated and not on login/register
  if (cartBtn) {
    if (!isAuthPage && user && user.role === 'client') {
      cartBtn.style.display = 'inline-flex';
    } else {
      cartBtn.style.display = 'none';
    }
  }

  // User pill / Auth buttons
  if (container) {
    if (!isAuthPage && user) {
      const isAdm = user.role === 'admin';
      const roleBadge = isAdm ? 'Admin' : 'Client';
      const roleColor = isAdm ? '#d97706' : '#0284c7';
      const initials = isAdm ? 'AD' : (user.name ? user.name.slice(0, 2).toUpperCase() : 'CL');

      container.innerHTML = `
        <div class="user-pill">
          <div class="user-avatar" style="background:${isAdm ? 'linear-gradient(135deg, #f59e0b, #ef4444)' : 'linear-gradient(135deg, #06b6d4, #6366f1)'};">
            ${initials}
          </div>
          <div style="display:flex; flex-direction:column; line-height:1.2;">
            <strong style="font-size:0.85rem; color:var(--text-main);">${escapeHtml(user.name)}</strong>
            <span style="font-size:0.7rem; font-weight:700; color:${roleColor};">${roleBadge}</span>
          </div>
          <button onclick="handleLogout()" class="btn btn-sm btn-secondary" style="padding:4px 8px; margin-left:6px; font-size:0.75rem;" title="Sign out">
            Logout
          </button>
        </div>
      `;
    } else {
      container.innerHTML = `
        <a href="/login" class="btn btn-sm btn-secondary">Sign In</a>
        <a href="/register" class="btn btn-sm btn-primary">Register</a>
      `;
    }
  }
}

function handleLogout() {
  setCurrentUser(null);
  showToast('You have been logged out.', 'info');
  setTimeout(() => {
    window.location.replace('/login');
  }, 250);
}

// Authentication Guards for Protected Routes
function checkAuthGuard() {
  const path = window.location.pathname;
  const user = getCurrentUser();

  const isPublicPage = path === '/login' || path === '/register';
  if (!isPublicPage && !user) {
    window.location.replace('/login');
    return;
  }

  // Admin access guard
  if (path.startsWith('/admin') && user && user.role !== 'admin') {
    window.location.replace('/catalog');
    return;
  }
}

// Toast Notifications
function showToast(message, type = 'info', duration = 3500) {
  let container = document.getElementById('toastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  
  let iconSvg = '';
  if (type === 'success') {
    iconSvg = `<svg width="20" height="20" fill="none" stroke="#10b981" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
  } else if (type === 'warning') {
    iconSvg = `<svg width="20" height="20" fill="none" stroke="#f59e0b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`;
  } else {
    iconSvg = `<svg width="20" height="20" fill="none" stroke="#6366f1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
  }

  toast.innerHTML = `
    <span>${iconSvg}</span>
    <div style="flex:1;">${message}</div>
    <button onclick="this.parentElement.remove()" style="background:none; border:none; color:var(--text-dim); cursor:pointer; display:inline-flex; align-items:center; padding:2px;" aria-label="Close notification"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
  `;

  container.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(30px)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

// Dialog light dismiss fallback (Modern Web Guidance)
function setupDialogFallbacks() {
  document.querySelectorAll('dialog').forEach(dialog => {
    if (!('closedBy' in HTMLDialogElement.prototype)) {
      dialog.addEventListener('click', (event) => {
        if (event.target !== dialog) return;
        const rect = dialog.getBoundingClientRect();
        const isDialogContent = (
          rect.top <= event.clientY &&
          event.clientY <= rect.top + rect.height &&
          rect.left <= event.clientX &&
          event.clientX <= rect.left + rect.width
        );
        if (isDialogContent) return;
        dialog.close();
      });
    }
  });
}

// Escape HTML helper
function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

// Theme Management (Default: Light Theme)
function getTheme() {
  return localStorage.getItem('demo_theme') || 'light';
}

function setTheme(theme) {
  document.documentElement.setAttribute('data-theme', theme);
  localStorage.setItem('demo_theme', theme);
  updateThemeButtonUI(theme);
}

function toggleTheme() {
  const current = getTheme();
  const next = current === 'dark' ? 'light' : 'dark';
  setTheme(next);
  showToast(`Switched to <strong>${next === 'dark' ? 'Dark' : 'Light'} Mode</strong>`, 'info', 2200);
}

function updateThemeButtonUI(theme) {
  const labelEl = document.getElementById('themeLabelText');
  if (labelEl) {
    labelEl.textContent = theme === 'dark' ? 'Dark' : 'Light';
  }
}

function initTheme() {
  const theme = getTheme();
  setTheme(theme);
}

// Initial Run
document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  checkAuthGuard();
  updateCartBadge();
  renderUserNav();
  setupDialogFallbacks();
  
  // Specific page initializations
  if (document.getElementById('productsGrid') && typeof renderProductsGrid === 'function') {
    renderProductsGrid();
  }
  if (document.getElementById('cartItemsList') && typeof renderCartPage === 'function') {
    renderCartPage();
  }
  if ((document.getElementById('checkoutSummaryContainer') || document.getElementById('checkoutItemsList')) && typeof renderCheckoutSummary === 'function') {
    renderCheckoutSummary();
  }
  if (document.getElementById('adminOrdersTable') && typeof renderAdminOrdersPage === 'function') {
    renderAdminOrdersPage();
  }
});
