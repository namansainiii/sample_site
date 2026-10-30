@extends('layouts.app')

@section('title', 'Request a Bulk Quote')

@section('content')
@php($user = auth()->user())
<div class="container" style="padding-bottom:40px;">
    <div class="layout-2">
        <div class="card">
            <div class="card-head"><h3>Request a bulk / project quote</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('quotes.store') }}" class="form-grid">
                    @csrf
                    <div class="field">
                        <label for="name">Your name</label>
                        <input class="input" id="name" name="name" required value="{{ old('name', $user?->name) }}">
                    </div>
                    <div class="field">
                        <label for="company_name">Company (optional)</label>
                        <input class="input" id="company_name" name="company_name" value="{{ old('company_name', $user?->company_name) }}">
                    </div>
                    <div class="field">
                        <label for="phone">Mobile</label>
                        <input class="input" id="phone" name="phone" required inputmode="numeric" maxlength="10" value="{{ old('phone', $user?->phone) }}">
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input class="input" type="email" id="email" name="email" required value="{{ old('email', $user?->email) }}">
                    </div>
                    <div class="field span-2">
                        <label for="requirements">What do you need?</label>
                        <textarea class="input" id="requirements" name="requirements" rows="8" required placeholder="e.g.&#10;M12 × 75 hex bolt 8.8 — 5,000 pcs&#10;M12 nylock nut — 5,000 pcs&#10;Delivery to Pune by 15th, need test certificate">{{ old('requirements', $product ? $product->name.' — quantity: ' : '') }}</textarea>
                        <span class="hint">List size, grade/material, quantity and delivery location. Paste straight from your BOQ.</span>
                    </div>
                    <div class="span-2">
                        <button class="btn btn-primary btn-lg" type="submit">Send quote request</button>
                    </div>
                </form>
            </div>
        </div>

        <aside class="stack">
            <div class="callout" style="grid-template-columns:1fr;">
                <div>
                    <span class="eyebrow">Why buy in bulk from us</span>
                    <h2>Factory-direct pricing</h2>
                    <p>✓ Reply within 24 working hours<br>✓ Mill test certificates on request<br>✓ Custom lengths &amp; special grades<br>✓ Credit terms for registered businesses</p>
                </div>
            </div>
            <div class="card card-body">
                <h3>Prefer to talk?</h3>
                <p class="muted">Call or WhatsApp our sales desk.</p>
                <a class="btn btn-dark btn-block" href="tel:{{ preg_replace('/\s+/', '', config('store.phone')) }}">📞 {{ config('store.phone') }}</a>
                <a class="btn btn-whatsapp btn-block" style="margin-top:8px;" target="_blank" rel="noopener" href="https://wa.me/{{ config('store.whatsapp') }}?text={{ rawurlencode('Hi, I need a bulk quote.') }}">WhatsApp sales</a>
            </div>
        </aside>
    </div>
</div>
@endsection
