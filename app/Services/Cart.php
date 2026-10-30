<?php

namespace App\Services;

use App\Models\ProductVariant;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * Session-backed shopping cart keyed by variant id. Prices are never stored in
 * the session: every read re-prices against the database so admin price and
 * stock changes apply immediately.
 */
class Cart
{
    private const SESSION_KEY = 'cart';

    public function __construct(private Session $session) {}

    /**
     * @return array<int, int> variant id => quantity (packs)
     */
    public function raw(): array
    {
        return $this->session->get(self::SESSION_KEY, []);
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    public function isEmpty(): bool
    {
        return $this->raw() === [];
    }

    /**
     * Add packs of a variant, clamping to the variant's minimum order and stock.
     * Returns the quantity now in the cart.
     */
    public function add(ProductVariant $variant, int $quantity): int
    {
        $items = $this->raw();
        $current = $items[$variant->id] ?? 0;

        return $this->put($variant, $current + $quantity);
    }

    public function put(ProductVariant $variant, int $quantity): int
    {
        $items = $this->raw();
        $quantity = max($quantity, $variant->min_order_qty);
        $quantity = min($quantity, $variant->stock);

        if ($quantity <= 0) {
            unset($items[$variant->id]);
        } else {
            $items[$variant->id] = $quantity;
        }

        $this->session->put(self::SESSION_KEY, $items);

        return $quantity;
    }

    public function remove(int $variantId): void
    {
        $items = $this->raw();
        unset($items[$variantId]);
        $this->session->put(self::SESSION_KEY, $items);
    }

    public function clear(): void
    {
        $this->session->forget(self::SESSION_KEY);
    }

    /**
     * Priced cart lines. Variants that were deleted or whose product was
     * deactivated drop out silently.
     *
     * @return Collection<int, array{variant: ProductVariant, quantity: int, unit_price: float, line_total: float, gst_rate: float, gst_amount: float}>
     */
    public function lines(): Collection
    {
        $items = $this->raw();

        if ($items === []) {
            return collect();
        }

        return ProductVariant::query()
            ->with(['product.category', 'priceTiers'])
            ->whereIn('id', array_keys($items))
            ->whereHas('product', fn ($query) => $query->active())
            ->get()
            ->map(function (ProductVariant $variant) use ($items): array {
                $quantity = $items[$variant->id];
                $unitPrice = $variant->unitPriceFor($quantity);
                $lineTotal = round($unitPrice * $quantity, 2);
                $gstRate = (float) $variant->product->gst_rate;

                return [
                    'variant' => $variant,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'gst_rate' => $gstRate,
                    'gst_amount' => round($lineTotal * $gstRate / 100, 2),
                ];
            })
            ->values();
    }

    /**
     * @param  Collection<int, array{line_total: float, gst_amount: float}>|null  $lines
     * @return array{subtotal: float, gst: float, shipping: float, total: float, free_shipping_gap: float}
     */
    public function totals(?Collection $lines = null): array
    {
        $lines ??= $this->lines();
        $subtotal = round($lines->sum('line_total'), 2);
        $gst = round($lines->sum('gst_amount'), 2);
        $threshold = (float) config('store.free_shipping_threshold');
        $shipping = ($subtotal === 0.0 || $subtotal >= $threshold) ? 0.0 : (float) config('store.shipping_fee');

        return [
            'subtotal' => $subtotal,
            'gst' => $gst,
            'shipping' => $shipping,
            'total' => round($subtotal + $gst + $shipping, 2),
            'free_shipping_gap' => max(0, round($threshold - $subtotal, 2)),
        ];
    }
}
