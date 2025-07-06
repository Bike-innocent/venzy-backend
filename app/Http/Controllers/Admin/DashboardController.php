<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function summary()
    {
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalCustomers = User::count();
        // $totalRevenue = Order::whereIn('status', [ 'delivered'])
        $totalRevenue = Order::whereIn('status', ['processing', 'shipped', 'delivered'])
            ->sum('total_amount');

        return response()->json([
            'orders' => $totalOrders,
            'products' => $totalProducts,
            'customers' => $totalCustomers,
            'revenue' => $totalRevenue,
        ]);
    }



    public function salesChart()
    {
        $startDate = Carbon::now()->subDays(30); // last 30 days

        $sales = DB::table('orders')
            ->selectRaw('DATE(order_date) as date, SUM(total_amount) as total_sales')
            ->where('status', '!=', 'cancelled') // ignore cancelled orders
            ->whereDate('order_date', '>=', $startDate)
            ->groupBy(DB::raw('DATE(order_date)'))
            ->orderBy('date')
            ->get();

        // Format for frontend charting library: labels + data
        $formatted = [
            'labels' => $sales->pluck('date'),
            'data' => $sales->pluck('total_sales'),
        ];

        return response()->json($formatted);
    }

  

}




















// ->selectRaw('YEARWEEK(order_date, 1) as week, SUM(total_amount) as total_sales')



// ->selectRaw('DATE_FORMAT(order_date, "%Y-%m") as month, SUM(total_amount) as total_sales')







//   public function topProducts()
//     {
//         $topProducts = DB::table('order_items')
//             ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
//             ->groupBy('product_id')
//             ->orderByDesc('total_quantity')
//             ->limit(10)
//             ->get();

//         $products = Product::whereIn('id', $topProducts->pluck('product_id'))->get();

//         return response()->json($products);
//     }

//     public function topCustomers()
//     {
//         $topCustomers = DB::table('orders')
//             ->select('user_id', DB::raw('COUNT(*) as order_count'))
//             ->groupBy('user_id')
//             ->orderByDesc('order_count')
//             ->limit(10)
//             ->get();

//         $customers = User::whereIn('id', $topCustomers->pluck('user_id'))->get();

//         return response()->json($customers);
//     }

//     public function recentOrders()
//     {
//         $orders = Order::with('user')
//             ->orderByDesc('created_at')
//             ->take(10)
//             ->get();

//         return response()->json($orders);
//     }

//     public function recentCustomers()
//     {
//         $customers = User::orderByDesc('created_at')
//             ->take(10)
//             ->get();

//         return response()->json($customers);
//     }