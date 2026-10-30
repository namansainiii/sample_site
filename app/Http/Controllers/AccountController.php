<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function orders(Request $request): View
    {
        return view('account.orders', [
            'orders' => $request->user()->orders()->withCount('items')->latest()->paginate(10),
        ]);
    }

    public function showOrder(Request $request, Order $order): View
    {
        $this->authorizeOrder($request, $order);

        return view('account.order', ['order' => $order->load('items.variant.product')]);
    }

    public function invoice(Request $request, Order $order): View
    {
        $this->authorizeOrder($request, $order);

        return view('account.invoice', ['order' => $order->load('items', 'user')]);
    }

    /**
     * Customers may only see their own orders; admins may see any.
     */
    private function authorizeOrder(Request $request, Order $order): void
    {
        $user = $request->user();

        abort_unless($user->is_admin || $order->user_id === $user->id, 404);
    }
}
