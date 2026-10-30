<?php

namespace App\Http\Controllers\Admin;

use App\Enums\QuoteStatus;
use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuoteRequestController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.quotes.index', [
            'quotes' => QuoteRequest::query()
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
                ->latest()
                ->paginate(25)
                ->withQueryString(),
        ]);
    }

    public function show(QuoteRequest $quote): View
    {
        return view('admin.quotes.show', ['quote' => $quote]);
    }

    public function update(Request $request, QuoteRequest $quote): RedirectResponse
    {
        $quote->update($request->validate([
            'status' => ['required', Rule::enum(QuoteStatus::class)],
            'quoted_amount' => ['nullable', 'numeric', 'min:0'],
            'admin_reply' => ['nullable', 'string', 'max:5000'],
        ]));

        return back()->with('success', 'Quote updated.');
    }
}
