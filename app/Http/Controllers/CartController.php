<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Services\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private Cart $cart) {}

    public function index(): View
    {
        $lines = $this->cart->lines();

        return view('cart.index', [
            'lines' => $lines,
            'totals' => $this->cart->totals($lines),
        ]);
    }

    public function add(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        $variant = ProductVariant::with('product')->findOrFail($data['variant_id']);
        abort_unless($variant->product->is_active, 404);

        if ($variant->stock < 1) {
            return $this->respond($request, false, 'Sorry, this size is out of stock.');
        }

        $quantity = $this->cart->add($variant, $data['quantity']);
        $message = "{$variant->product->name} ({$variant->size}) added to cart.";

        if ($quantity >= $variant->stock) {
            $message .= " Only {$variant->stock} pack(s) available, so your quantity was capped.";
        }

        return $this->respond($request, true, $message);
    }

    public function update(Request $request, ProductVariant $variant): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:10000']]);

        if ($data['quantity'] === 0) {
            $this->cart->remove($variant->id);
        } else {
            $this->cart->put($variant, $data['quantity']);
        }

        return redirect()->route('cart.index');
    }

    public function remove(ProductVariant $variant): RedirectResponse
    {
        $this->cart->remove($variant->id);

        return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
    }

    /**
     * B2B "quick order": paste lines of "SKU, quantity" and add them all at once.
     */
    public function quickOrder(Request $request): RedirectResponse
    {
        $data = $request->validate(['lines' => ['required', 'string', 'max:5000']]);

        $wanted = [];
        $invalid = [];

        foreach (preg_split('/\r\n|\r|\n/', trim($data['lines'])) as $row) {
            if (trim($row) === '') {
                continue;
            }

            if (! preg_match('/^\s*([A-Za-z0-9\-_.\/]+)\s*[,;\t ]\s*(\d+)\s*$/', $row, $match)) {
                $invalid[] = trim($row);

                continue;
            }

            $sku = strtoupper($match[1]);
            $wanted[$sku] = ($wanted[$sku] ?? 0) + (int) $match[2];
        }

        $variants = ProductVariant::with('product')
            ->whereIn('sku', array_keys($wanted))
            ->whereHas('product', fn ($query) => $query->active())
            ->get()
            ->keyBy(fn (ProductVariant $variant) => strtoupper($variant->sku));

        $added = 0;
        foreach ($wanted as $sku => $quantity) {
            $variant = $variants[$sku] ?? null;

            if (! $variant || $variant->stock < 1 || $quantity < 1) {
                $invalid[] = $sku;

                continue;
            }

            $this->cart->add($variant, $quantity);
            $added++;
        }

        $redirect = redirect()->route('cart.index');

        if ($added > 0) {
            $redirect->with('success', "{$added} item(s) added from your quick order.");
        }

        if ($invalid !== []) {
            $redirect->with('warning', 'Could not add: '.implode(', ', array_slice($invalid, 0, 10)).' (unknown SKU, bad format or out of stock).');
        }

        return $redirect;
    }

    private function respond(Request $request, bool $ok, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => $ok, 'message' => $message, 'count' => $this->cart->count()], $ok ? 200 : 422);
        }

        return back()->with($ok ? 'success' : 'warning', $message);
    }
}
