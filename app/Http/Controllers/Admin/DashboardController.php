<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\QuoteStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\QuoteRequest;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $billable = Order::query()->where('status', '!=', OrderStatus::Cancelled);

        $salesByDay = (clone $billable)
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->get(['created_at', 'total'])
            ->groupBy(fn (Order $order) => $order->created_at->format('Y-m-d'))
            ->map(fn ($orders) => round($orders->sum('total'), 2));

        $chart = collect(range(13, 0))->mapWithKeys(function (int $daysAgo) use ($salesByDay) {
            $day = now()->subDays($daysAgo);

            return [$day->format('d M') => (float) ($salesByDay[$day->format('Y-m-d')] ?? 0)];
        });

        return view('admin.dashboard', [
            'stats' => [
                'revenue_today' => (clone $billable)->whereDate('created_at', today())->sum('total'),
                'revenue_month' => (clone $billable)->where('created_at', '>=', now()->startOfMonth())->sum('total'),
                'orders_open' => Order::whereIn('status', [OrderStatus::Pending, OrderStatus::Confirmed, OrderStatus::Packed])->count(),
                'quotes_new' => QuoteRequest::where('status', QuoteStatus::New)->count(),
                'products' => Product::count(),
            ],
            'chart' => $chart,
            'recentOrders' => Order::with('user')->latest()->take(8)->get(),
            'lowStock' => ProductVariant::with('product')
                ->where('stock', '<=', config('store.low_stock_threshold'))
                ->orderBy('stock')
                ->take(10)
                ->get(),
        ]);
    }
}
