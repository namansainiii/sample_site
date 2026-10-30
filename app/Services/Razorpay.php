<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/**
 * Minimal Razorpay Orders API client. Uses the plain REST API so no extra
 * Composer package is needed (keeps shared-hosting deploys simple).
 */
class Razorpay
{
    public function isConfigured(): bool
    {
        return filled(config('services.razorpay.key')) && filled(config('services.razorpay.secret'));
    }

    public function key(): string
    {
        return (string) config('services.razorpay.key');
    }

    /**
     * Create a Razorpay order for our order total and remember its id.
     *
     * @throws RequestException
     */
    public function createOrder(Order $order): string
    {
        $response = Http::withBasicAuth($this->key(), (string) config('services.razorpay.secret'))
            ->timeout(15)
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => (int) round((float) $order->total * 100),
                'currency' => 'INR',
                'receipt' => $order->order_number,
                'notes' => ['order_number' => $order->order_number],
            ])
            ->throw();

        $razorpayOrderId = (string) $response->json('id');
        $order->update(['razorpay_order_id' => $razorpayOrderId]);

        return $razorpayOrderId;
    }

    /**
     * Verify the checkout signature Razorpay returns to the browser.
     */
    public function signatureIsValid(string $razorpayOrderId, string $paymentId, string $signature): bool
    {
        $expected = hash_hmac('sha256', $razorpayOrderId.'|'.$paymentId, (string) config('services.razorpay.secret'));

        return hash_equals($expected, $signature);
    }
}
