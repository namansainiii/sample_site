<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\QuoteRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function create(Request $request): View
    {
        $product = $request->filled('product')
            ? Product::active()->where('slug', $request->string('product'))->first()
            : null;

        return view('quotes.create', ['product' => $product]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^[6-9][0-9]{9}$/'],
            'email' => ['required', 'email', 'max:255'],
            'requirements' => ['required', 'string', 'min:10', 'max:5000'],
        ], ['phone.regex' => 'Enter a valid 10-digit mobile number.']);

        QuoteRequest::create([...$data, 'user_id' => $request->user()?->id]);

        return redirect()->route('quotes.create')->with('success', 'Thanks! Our sales team will send you a quote within 24 hours.');
    }
}
