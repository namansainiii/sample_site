<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlaceOrder
{
    public function __construct(private Cart $cart) {}

    /**
     * Turn the cart into an order, reserving stock atomically. Stock rows are
     * locked so two buyers can never both take the last packs.
     *
     * @param  array{shipping_name: string, shipping_phone: string, shipping_address: string, shipping_city: string, shipping_state: string, shipping_pincode: string, company_name?: string|null, gstin?: string|null, notes?: string|null}  $details
     *
     * @throws ValidationException when the cart is empty or stock ran out
     */
    public function handle(User $user, array $details, PaymentMethod $paymentMethod): Order
    {
        $order = DB::transaction(function () use ($user, $details, $paymentMethod): Order {
            $lines = $this->cart->lines();

            if ($lines->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
            }

            $variants = ProductVariant::query()
                ->whereIn('id', $lines->pluck('variant.id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($lines as $line) {
                $variant = $variants[$line['variant']->id];

                if ($variant->stock < $line['quantity']) {
                    throw ValidationException::withMessages([
                        'cart' => "Only {$variant->stock} pack(s) of {$line['variant']->product->name} ({$variant->size}) are left in stock.",
                    ]);
                }
            }

            $totals = $this->cart->totals($lines);

            $order = Order::create([
                ...$details,
                'user_id' => $user->id,
                'status' => OrderStatus::Pending,
                'payment_method' => $paymentMethod,
                'payment_status' => PaymentStatus::Pending,
                'subtotal' => $totals['subtotal'],
                'gst_amount' => $totals['gst'],
                'shipping_fee' => $totals['shipping'],
                'total' => $totals['total'],
            ]);

            $order->update(['order_number' => 'BM'.now()->format('ymd').str_pad((string) $order->id, 5, '0', STR_PAD_LEFT)]);

            foreach ($lines as $line) {
                $variant = $line['variant'];

                $order->items()->create([
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_label' => $variant->label(),
                    'sku' => $variant->sku,
                    'hsn_code' => $variant->product->hsn_code,
                    'unit_price' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                    'gst_rate' => $line['gst_rate'],
                    'line_total' => $line['line_total'],
                ]);

                $variants[$variant->id]->decrement('stock', $line['quantity']);
            }

            return $order;
        });

        $this->cart->clear();

        return $order;
    }

    /**
     * Put an order's packs back on the shelf (used when an order is cancelled).
     */
    public static function restock(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->product_variant_id) {
                ProductVariant::whereKey($item->product_variant_id)->increment('stock', $item->quantity);
            }
        }
    }
}
