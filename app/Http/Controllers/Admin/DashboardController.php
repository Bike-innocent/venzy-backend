<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
     public function summary()
    {
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalCustomers = User::count();

        $totalRevenue = Order::whereIn('status', ['processing', 'shipped', 'delivered'])
            ->sum('total_amount');

        return response()->json([
            'orders' => $totalOrders,
            'products' => $totalProducts,
            'customers' => $totalCustomers,
            'revenue' => $totalRevenue,
        ]);
    }
}