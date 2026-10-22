@extends('layouts.app')

@section('title', 'Sign In - Admin & Client Gateway | Aura Store')

@section('content')
<div class="container">
    <div class="auth-wrapper" style="flex-direction: column; max-width: 780px; margin: 0 auto;">
        
        <!-- Welcome Header -->
        <div class="auth-header" style="text-align: center; margin-bottom: 24px;">
            <div class="brand-icon" style="margin: 0 auto 16px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/>
                </svg>
            </div>
            <h1 class="auth-title">Sign In to Aura Store</h1>
            <p class="auth-subtitle">Select an account portal to explore the store as a <strong>Store Administrator</strong> or a <strong>Client / Customer</strong>.</p>
        </div>

        <!-- 2 Login Roles Selection & Quick-Fills -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; width: 100%; margin-bottom: 28px;">
            
            <!-- Role 1: Admin Login Card -->
            <div class="glass-card" style="padding: 24px; border: 2px solid rgba(245, 158, 11, 0.3); background: var(--bg-card); position:relative; overflow:hidden;" id="adminCardBox">
                <div style="position:absolute; top:12px; right:12px; font-size:0.7rem; font-weight:700; text-transform:uppercase; background:rgba(245, 158, 11, 0.15); color:#d97706; padding:3px 8px; border-radius:6px; border:1px solid rgba(245, 158, 11, 0.3);">
                    Portal 1
                </div>
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
                    <div style="width:40px; height:40px; border-radius:10px; background:linear-gradient(135deg, #f59e0b, #ef4444); color:#fff; display:flex; align-items:center; justify-content:center;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <div>
                        <h2 style="font-size:1.2rem; font-weight:700; margin:0;">Store Administrator</h2>
                        <span style="font-size:0.8rem; color:var(--text-dim);">Full Store Management</span>
                    </div>
                </div>
                <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:16px; min-height:42px;">
                    Privileged access to <strong>Add New Products</strong> and inspect customer purchases on the <strong>Check Orders Page</strong>.
                </p>
                <div style="display:flex; gap:10px;">
                    <button type="button" class="btn btn-sm btn-quickfill" style="flex:1;" onclick="quickFillAdmin()">
                        Quick Fill Admin
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" style="flex:1; background:linear-gradient(135deg, #f59e0b, #d97706);" onclick="oneClickAdmin()">
                        Sign In as Admin
                    </button>
                </div>
            </div>

            <!-- Role 2: Client / Customer Login Card -->
            <div class="glass-card" style="padding: 24px; border: 2px solid rgba(6, 182, 212, 0.3); background: var(--bg-card); position:relative; overflow:hidden;" id="clientCardBox">
                <div style="position:absolute; top:12px; right:12px; font-size:0.7rem; font-weight:700; text-transform:uppercase; background:rgba(6, 182, 212, 0.15); color:#0284c7; padding:3px 8px; border-radius:6px; border:1px solid rgba(6, 182, 212, 0.3);">
                    Portal 2
                </div>
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
                    <div style="width:40px; height:40px; border-radius:10px; background:linear-gradient(135deg, #06b6d4, #6366f1); color:#fff; display:flex; align-items:center; justify-content:center;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <div>
                        <h2 style="font-size:1.2rem; font-weight:700; margin:0;">Client / Shopper</h2>
                        <span style="font-size:0.8rem; color:var(--text-dim);">Storefront Customer</span>
                    </div>
                </div>
                <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:16px; min-height:42px;">
                    Access to <strong>Browse Catalog</strong>, add items to <strong>Cart</strong>, and complete <strong>Checkout &amp; Payment</strong>.
                </p>
                <div style="display:flex; gap:10px;">
                    <button type="button" class="btn btn-sm btn-quickfill" style="flex:1; background:rgba(6, 182, 212, 0.12); color:#0284c7; border-color:rgba(6, 182, 212, 0.4);" onclick="quickFillClient()">
                        Quick Fill Client
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" style="flex:1;" onclick="oneClickClient()">
                        Sign In as Client
                    </button>
                </div>
            </div>

        </div>

        <!-- Custom Credential Form Box -->
        <div class="auth-card" style="max-width: 480px; width: 100%;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 20px;">
                <span style="font-size:0.9rem; font-weight:700; color:var(--text-main);">Sign In Form</span>
                <span id="selectedRoleBadge" style="font-size:0.75rem; font-weight:700; padding:3px 8px; border-radius:6px; background:rgba(99, 102, 241, 0.15); color:var(--primary);">
                    Role: Client Selected
                </span>
            </div>

            <form id="loginForm" onsubmit="handleLoginSubmit(event)">
                <input type="hidden" id="loginRole" value="client">

                <div class="form-group">
                    <label for="loginEmail" class="form-label">Email Address</label>
                    <input type="email" id="loginEmail" class="form-control" placeholder="client@aura.store" required autocomplete="email" value="client@aura.store">
                </div>

                <div class="form-group">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <label for="loginPassword" class="form-label" style="margin-bottom:0;">Password</label>
                        <a href="javascript:void(0)" onclick="showToast('Demo mode: quick fill buttons above populate valid passwords.', 'info')" style="font-size:0.78rem; color:var(--primary);">Forgot password?</a>
                    </div>
                    <input type="password" id="loginPassword" class="form-control" placeholder="Enter password" required autocomplete="current-password" value="ClientPass2026!">
                </div>

                <div class="form-group" style="display:flex; justify-content:space-between; align-items:center;">
                    <label class="form-check">
                        <input type="checkbox" id="rememberMe" checked>
                        <span>Remember my login</span>
                    </label>
                </div>

                <div style="display:flex; flex-direction:column; gap:12px; margin-top:20px;">
                    <button type="submit" class="btn btn-primary btn-block" id="signInSubmitBtn">
                        Sign In
                    </button>
                </div>
            </form>

            <div style="text-align:center; margin-top:20px; font-size:0.875rem; color:var(--text-muted);">
                Need a new dummy profile? 
                <a href="{{ route('register') }}" style="font-weight:600; color:var(--primary);">Register here</a>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
function quickFillAdmin() {
    document.getElementById('loginRole').value = 'admin';
    document.getElementById('loginEmail').value = 'admin@aura.store';
    document.getElementById('loginPassword').value = 'AdminPass2026!';

    const badge = document.getElementById('selectedRoleBadge');
    badge.textContent = 'Role: Administrator Selected';
    badge.style.background = 'rgba(245, 158, 11, 0.2)';
    badge.style.color = '#d97706';

    highlightFields();
    showToast('Administrator credentials pre-filled.', 'info');
}

function quickFillClient() {
    document.getElementById('loginRole').value = 'client';
    document.getElementById('loginEmail').value = 'client@aura.store';
    document.getElementById('loginPassword').value = 'ClientPass2026!';

    const badge = document.getElementById('selectedRoleBadge');
    badge.textContent = 'Role: Client Selected';
    badge.style.background = 'rgba(6, 182, 212, 0.2)';
    badge.style.color = '#0284c7';

    highlightFields();
    showToast('Client credentials pre-filled.', 'info');
}

function highlightFields() {
    const email = document.getElementById('loginEmail');
    const pass = document.getElementById('loginPassword');
    [email, pass].forEach(el => {
        el.style.borderColor = 'var(--primary)';
        el.style.backgroundColor = 'var(--bg-glass)';
        setTimeout(() => {
            el.style.borderColor = '';
            el.style.backgroundColor = '';
        }, 500);
    });
}

function oneClickAdmin() {
    quickFillAdmin();
    setTimeout(() => {
        handleLoginSubmit(new Event('submit'));
    }, 150);
}

function oneClickClient() {
    quickFillClient();
    setTimeout(() => {
        handleLoginSubmit(new Event('submit'));
    }, 150);
}

function handleLoginSubmit(e) {
    e.preventDefault();
    const email = document.getElementById('loginEmail').value.trim();
    const role = document.getElementById('loginRole').value;
    const btn = document.getElementById('signInSubmitBtn');

    btn.disabled = true;
    btn.innerHTML = `<span class="auth-spinner" style="display:inline-block; width:14px; height:14px; border:2px solid rgba(255,255,255,0.3); border-top-color:#fff; border-radius:50%; animation:spin 0.8s linear infinite; vertical-align:middle; margin-right:8px;"></span> Signing in...`;

    setTimeout(() => {
        let userObj = null;
        if (role === 'admin') {
            userObj = {
                name: 'Vikram Mehta (Admin)',
                email: email,
                role: 'admin'
            };
        } else {
            userObj = {
                name: 'Ananya Roy (Client)',
                email: email,
                role: 'client'
            };
        }

        setCurrentUser(userObj);
        showToast(`Welcome, <strong>${escapeHtml(userObj.name)}</strong>!`, 'success');

        setTimeout(() => {
            window.location.replace("{{ route('catalog') }}");
        }, 300);
    }, 300);
}
</script>
<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
@endsection

