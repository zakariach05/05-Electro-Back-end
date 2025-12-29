<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        $totalOrders = Order::count();
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');
        $totalProducts = Product::count();
        
        $dailyOrders = Order::whereDate('created_at', now()->today())->count();
        $lateOrders = Order::where('status', 'pending')->where('created_at', '<', now()->subHours(48))->count();

        $paymentStats = [
            'paid' => Order::where('payment_status', 'paid')->sum('total_amount'),
            'unpaid' => Order::where('payment_status', 'unpaid')->sum('total_amount'),
            'refunded' => Order::where('payment_status', 'refunded')->sum('total_amount'),
        ];

        // Stats by city
        $cityStats = Order::select('customer_city', DB::raw('count(*) as count'))
            ->groupBy('customer_city')
            ->orderBy('count', 'desc')
            ->get();

        // Activity Logs
        $recentActivity = \App\Models\ActivityLog::with('user')->latest()->take(10)->get();

        return response()->json([
            'overview' => [
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'daily_orders' => $dailyOrders,
                'late_orders' => $lateOrders,
                'total_products' => $totalProducts,
            ],
            'payment' => $paymentStats,
            'cities' => $cityStats,
            'activity' => $recentActivity
        ]);
    }
}
