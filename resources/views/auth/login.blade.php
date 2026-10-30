@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="container auth-wrap">
    <div class="card auth-card">
        <div class="card-body">
            <h2>Welcome back</h2>
            <p class="muted">Log in to check out, track orders and download invoices.</p>
            <form method="POST" action="{{ route('login') }}" class="fields">
                @csrf
                <div class="field">
                    <label for="email">Email</label>
                    <input class="input" type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input class="input" type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                <label class="check"><input type="checkbox" name="remember" value="1"> Keep me logged in</label>
                <button class="btn btn-primary btn-lg btn-block" type="submit">Login</button>
            </form>
            <hr>
            <p style="margin:0; text-align:center;">New here? <a href="{{ route('register') }}" style="font-weight:700; text-decoration:underline;">Create an account</a></p>
        </div>
    </div>
</div>
@endsection
