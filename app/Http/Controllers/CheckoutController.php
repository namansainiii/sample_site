<?php

namespace App\Http\Controllers;

use App\Actions\PlaceOrder;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Cart;
use App\Services\Razorpay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class CheckoutController extends Controller
{
    public function __construct(private Cart $cart, private Razorpay $razorpay) {}

    public function show(Request $request): View|RedirectResponse
    {
        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            return redirect()->route('cart.index')->with('warning', 'Your cart is empty.');
        }

        $lastOrder = $request->user()->orders()->latest()->first();

        return view('checkout.index', [
            'lines' => $lines,
            'totals' => $this->cart->totals($lines),
            'onlinePaymentEnabled' => $this->razorpay->isConfigured(),
            'prefill' => $lastOrder,
        ]);
    }

    public function store(Request $request, PlaceOrder $placeOrder): RedirectResponse
    {
        $methods = $this->razorpay->isConfigured()
            ? [PaymentMethod::CashOnDelivery->value, PaymentMethod::Razorpay->value]
            : [PaymentMethod::CashOnDelivery->value];

        $request->merge(['gstin' => $request->filled('gstin') ? strtoupper(trim((string) $request->input('gstin'))) : null]);

        $data = $request->validate([
            'shipping_name' => ['required', 'string', 'max:255'],
            'shipping_phone' => ['required', 'regex:/^[6-9][0-9]{9}$/'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'shipping_city' => ['required', 'string', 'max:100'],
            'shipping_state' => ['required', Rule::in(config('store.states'))],
            'shipping_pincode' => ['required', 'regex:/^[1-9][0-9]{5}$/'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'gstin' => ['nullable', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', Rule::in($methods)],
        ], [
            'shipping_phone.regex' => 'Enter a valid 10-digit mobile number.',
            'shipping_pincode.regex' => 'Enter a valid 6-digit PIN code.',
            'gstin.regex' => 'Enter a valid 15-character GSTIN.',
        ]);

        $method = PaymentMethod::from($data['payment_method']);
        unset($data['payment_method']);

        $order = $placeOrder->handle($request->user(), $data, $method);

        if ($method === PaymentMethod::CashOnDelivery) {
            return redirect()->route('account.orders.show', $order)->with('success', 'Order placed! We will call you to confirm shortly.');
        }

        try {
            $this->razorpay->createOrder($order);
        } catch (Throwable $exception) {
            Log::error('Razorpay order creation failed', ['order' => $order->order_number, 'error' => $exception->getMessage()]);

            return redirect()->route('account.orders.show', $order)
                ->with('warning', 'We could not reach the payment gateway. Please retry payment from this page.');
        }

        return redirect()->route('checkout.pay', $order);
    }

    public function pay(Request $request, Order $order): View|RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        if (! $order->awaitingOnlinePayment()) {
            return redirect()->route('account.orders.show', $order);
        }

        if (! $order->razorpay_order_id) {
            try {
                $this->razorpay->createOrder($order);
            } catch (Throwable $exception) {
                Log::error('Razorpay order creation failed', ['order' => $order->order_number, 'error' => $exception->getMessage()]);

                return redirect()->route('account.orders.show', $order)->with('warning', 'Payment gateway unavailable. Please try again in a minute.');
            }
        }

        return view('checkout.pay', ['order' => $order, 'razorpayKey' => $this->razorpay->key()]);
    }

    public function verifyPayment(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $data = $request->validate([
            'razorpay_payment_id' => ['required', 'string', 'max:100'],
            'razorpay_order_id' => ['required', 'string', 'max:100'],
            'razorpay_signature' => ['required', 'string', 'max:200'],
        ]);

        $valid = $data['razorpay_order_id'] === $order->razorpay_order_id
            && $this->razorpay->signatureIsValid($data['razorpay_order_id'], $data['razorpay_payment_id'], $data['razorpay_signature']);

        if (! $valid) {
            Log::warning('Razorpay signature mismatch', ['order' => $order->order_number]);

            return redirect()->route('account.orders.show', $order)->with('warning', 'Payment could not be verified. If money was deducted, contact us with your order number.');
        }

        DB::transaction(function () use ($order, $data): void {
            $order->update([
                'payment_status' => PaymentStatus::Paid,
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'paid_at' => now(),
                'status' => $order->status === OrderStatus::Pending ? OrderStatus::Confirmed : $order->status,
            ]);
        });

        return redirect()->route('account.orders.show', $order)->with('success', 'Payment received. Your order is confirmed!');
    }
}
