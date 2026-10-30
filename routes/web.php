<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

// Storefront: open to everyone so search engines and new buyers can browse.
Route::get('/', [ShopController::class, 'home'])->name('home');
Route::get('/shop', [ShopController::class, 'catalog'])->name('shop');
Route::get('/shop/{category}', [ShopController::class, 'catalog'])->name('shop.category');
Route::get('/product/{product}', [ShopController::class, 'product'])->name('product.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/quick-order', [CartController::class, 'quickOrder'])->name('cart.quick-order');
Route::patch('/cart/{variant}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{variant}', [CartController::class, 'remove'])->name('cart.remove');

Route::get('/bulk-quote', [QuoteController::class, 'create'])->name('quotes.create');
Route::post('/bulk-quote', [QuoteController::class, 'store'])->middleware('throttle:5,1')->name('quotes.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/{order}/pay', [CheckoutController::class, 'pay'])->name('checkout.pay');
    Route::post('/checkout/{order}/verify', [CheckoutController::class, 'verifyPayment'])->name('checkout.verify');

    Route::get('/account/orders', [AccountController::class, 'orders'])->name('account.orders');
    Route::get('/account/orders/{order}', [AccountController::class, 'showOrder'])->name('account.orders.show');
    Route::get('/account/orders/{order}/invoice', [AccountController::class, 'invoice'])->name('account.orders.invoice');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');
    Route::resource('products', Admin\ProductController::class)->except('show');
    Route::resource('categories', Admin\CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('orders', Admin\OrderController::class)->only(['index', 'show', 'update']);
    Route::resource('quotes', Admin\QuoteRequestController::class)->only(['index', 'show', 'update']);
});
