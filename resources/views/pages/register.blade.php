@extends('layouts.app')

@section('title', 'Create Demo Account - Aura Store')

@section('content')
<div class="container">
    <div class="auth-wrapper">
        <div class="auth-card" style="max-width: 540px;">
            <div class="auth-header">
                <div class="brand-icon" style="margin: 0 auto 16px; background: linear-gradient(135deg, #06b6d4, #10b981);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <line x1="19" y1="8" x2="19" y2="14"/>
                        <line x1="22" y1="11" x2="16" y2="11"/>
                    </svg>
                </div>
                <h1 class="auth-title">Create Demo Account</h1>
                <p class="auth-subtitle">Register a new profile as a Client Shopper or a Store Administrator.</p>
            </div>

            <!-- Quick Fill Demo Registration Banner -->
            <div class="quick-fill-box" style="flex-direction:column; gap:8px; align-items:stretch;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <strong style="color:var(--quickfill-text); font-size:0.85rem;">Quick Fill Profiles:</strong>
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="button" class="btn btn-sm btn-quickfill" style="flex:1;" onclick="quickFillRegClient()">
                        Quick Fill Client
                    </button>
                    <button type="button" class="btn btn-sm btn-quickfill" style="flex:1;" onclick="quickFillRegAdmin()">
                        Quick Fill Admin
                    </button>
                </div>
            </div>

            <form id="registerForm" onsubmit="handleRegisterSubmit(event)">
                <div class="form-group">
                    <label for="regRole" class="form-label">Account Role *</label>
                    <select id="regRole" class="form-control" required onchange="handleRoleChange(this.value)">
                        <option value="client" selected>Client / Customer (Browse, Cart &amp; Checkout)</option>
                        <option value="admin">Store Administrator (Add Products &amp; Check Orders)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="regName" class="form-label">Full Name *</label>
                    <input type="text" id="regName" class="form-control" placeholder="Ananya Roy" required autocomplete="name" value="Ananya Roy">
                </div>

                <div class="form-group">
                    <label for="regEmail" class="form-label">Email Address *</label>
                    <input type="email" id="regEmail" class="form-control" placeholder="ananya.roy@example.in" required autocomplete="email" value="ananya.roy@example.in">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="regPassword" class="form-label">Password *</label>
                        <input type="password" id="regPassword" class="form-control" placeholder="Enter password" required autocomplete="new-password" value="ClientPass2026!">
                    </div>
                    <div class="form-group">
                        <label for="regConfirmPassword" class="form-label">Confirm Password *</label>
                        <input type="password" id="regConfirmPassword" class="form-control" placeholder="Confirm password" required autocomplete="new-password" value="ClientPass2026!">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" id="termsCheck" checked required>
                        <span>I accept the simulated demo terms and role permissions.</span>
                    </label>
                </div>

                <div style="display:flex; flex-direction:column; gap:12px; margin-top:20px;">
                    <button type="submit" class="btn btn-primary btn-block" id="regSubmitBtn">
                        Create Account
                    </button>
                </div>
            </form>

            <div style="text-align:center; margin-top:20px; font-size:0.875rem; color:var(--text-muted);">
                Already have an account? 
                <a href="{{ route('login') }}" style="font-weight:600; color:var(--primary);">Sign in here</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function quickFillRegClient() {
    document.getElementById('regRole').value = 'client';
    document.getElementById('regName').value = 'Ananya Roy';
    document.getElementById('regEmail').value = 'ananya.roy@example.in';
    document.getElementById('regPassword').value = 'ClientPass2026!';
    document.getElementById('regConfirmPassword').value = 'ClientPass2026!';
    showToast('Client details pre-filled.', 'info');
}

function quickFillRegAdmin() {
    document.getElementById('regRole').value = 'admin';
    document.getElementById('regName').value = 'Vikram Mehta';
    document.getElementById('regEmail').value = 'vikram.admin@aura.store';
    document.getElementById('regPassword').value = 'AdminPass2026!';
    document.getElementById('regConfirmPassword').value = 'AdminPass2026!';
    showToast('Admin details pre-filled.', 'info');
}

function handleRoleChange(role) {
    if (role === 'admin') {
        quickFillRegAdmin();
    } else {
        quickFillRegClient();
    }
}

function handleRegisterSubmit(e) {
    e.preventDefault();
    const role = document.getElementById('regRole').value;
    const name = document.getElementById('regName').value.trim();
    const email = document.getElementById('regEmail').value.trim();
    const pass = document.getElementById('regPassword').value;
    const confirm = document.getElementById('regConfirmPassword').value;

    if (pass !== confirm) {
        showToast('Passwords do not match.', 'warning');
        return;
    }

    const btn = document.getElementById('regSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = `<span style="display:inline-block; width:14px; height:14px; border:2px solid rgba(255,255,255,0.3); border-top-color:#fff; border-radius:50%; animation:spin 0.8s linear infinite; vertical-align:middle; margin-right:8px;"></span> Creating Account...`;

    setTimeout(() => {
        const demoUser = {
            name: name,
            email: email,
            role: role
        };

        setCurrentUser(demoUser);
        showToast(`Account registered as <strong>${role === 'admin' ? 'Administrator' : 'Client'}</strong>.`, 'success');
        
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
