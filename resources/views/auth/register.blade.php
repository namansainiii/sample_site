@extends('layouts.app')

@section('title', 'Create account')

@section('content')
<div class="container auth-wrap">
    <div class="card auth-card wide">
        <div class="card-body">
            <h2>Create your account</h2>
            <p class="muted">Takes 30 seconds. Add your GSTIN to get business invoices automatically.</p>
            <form method="POST" action="{{ route('register') }}" class="form-grid">
                @csrf
                <div class="field">
                    <label for="name">Full name</label>
                    <input class="input" id="name" name="name" value="{{ old('name') }}" required autocomplete="name">
                </div>
                <div class="field">
                    <label for="phone">Mobile number</label>
                    <input class="input" id="phone" name="phone" value="{{ old('phone') }}" required inputmode="numeric" maxlength="10" autocomplete="tel-national">
                </div>
                <div class="field span-2">
                    <label for="email">Email</label>
                    <input class="input" type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                </div>
                <div class="field">
                    <label for="company_name">Business name <small class="muted">(optional)</small></label>
                    <input class="input" id="company_name" name="company_name" value="{{ old('company_name') }}" autocomplete="organization">
                </div>
                <div class="field">
                    <label for="gstin">GSTIN <small class="muted">(optional)</small></label>
                    <input class="input mono" id="gstin" name="gstin" value="{{ old('gstin') }}" maxlength="15" style="text-transform:uppercase">
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input class="input" type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm password</label>
                    <input class="input" type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                </div>
                <div class="span-2">
                    <button class="btn btn-primary btn-lg btn-block" type="submit">Create account</button>
                    <p style="margin:14px 0 0; text-align:center;">Already registered? <a href="{{ route('login') }}" style="font-weight:700; text-decoration:underline;">Login</a></p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
