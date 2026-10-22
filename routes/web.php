<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Starting gateway: only login or registration first
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('/login', function () {
    return view('pages.login');
})->name('login');

Route::get('/register', function () {
    return view('pages.register');
})->name('register');

// Protected Store Routes: strictly locked until login or registration
Route::group(['middleware' => function (Request $request, Closure $next) {
    if (! $request->cookie('demo_logged_in') && ! $request->session()->has('demo_user')) {
        return redirect()->route('login');
    }

    return $next($request);
}], function () {
    // Store Catalog
    Route::get('/catalog', function () {
        return view('pages.home');
    })->name('catalog');

    // Client Cart & Checkout
    Route::get('/cart', function () {
        return view('pages.cart');
    })->name('cart');

    Route::get('/checkout', function () {
        return view('pages.checkout');
    })->name('checkout');

    // Admin Orders Management
    Route::get('/admin/orders', function () {
        return view('pages.admin-orders');
    })->name('admin.orders');
});
