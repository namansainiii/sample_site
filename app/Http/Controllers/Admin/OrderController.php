<?php

namespace App\Http\Controllers\Admin;

use App\Actions\PlaceOrder;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::with('user')
            ->withCount('items')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(fn ($inner) => $inner->where('order_number', 'like', $term)
                    ->orWhere('shipping_name', 'like', $term)
                    ->orWhere('shipping_phone', 'like', $term));
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.orders.index', ['orders' => $orders]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', ['order' => $order->load('items', 'user')]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'mark_cod_paid' => ['nullable', 'boolean'],
        ]);

        $newStatus = OrderStatus::from($data['status']);

        if ($order->status === OrderStatus::Cancelled && $newStatus !== OrderStatus::Cancelled) {
            return back()->with('warning', 'Cancelled orders cannot be reopened (stock was already returned). Ask the customer to re-order.');
        }

        DB::transaction(function () use ($order, $newStatus, $data, $request): void {
            if ($newStatus === OrderStatus::Cancelled && $order->status !== OrderStatus::Cancelled) {
                PlaceOrder::restock($order->load('items'));
            }

            $changes = ['status' => $newStatus, 'tracking_number' => $data['tracking_number'] ?? null];

            if ($newStatus === OrderStatus::Shipped && ! $order->shipped_at) {
                $changes['shipped_at'] = now();
            }

            if ($newStatus === OrderStatus::Delivered && ! $order->delivered_at) {
                $changes['delivered_at'] = now();
                $changes['shipped_at'] = $order->shipped_at ?? now();
            }

            if ($request->boolean('mark_cod_paid') && $order->payment_method === PaymentMethod::CashOnDelivery) {
                $changes['payment_status'] = PaymentStatus::Paid;
                $changes['paid_at'] = $order->paid_at ?? now();
            }

            $order->update($changes);
        });

        return back()->with('success', 'Order '.$order->order_number.' updated.');
    }
}
