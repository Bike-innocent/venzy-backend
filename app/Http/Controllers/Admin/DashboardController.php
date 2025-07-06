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



    // public function salesChart()
    // {
    //     $startDate = Carbon::now()->subDays(30); // last 30 days

    //     $sales = DB::table('orders')
    //         ->selectRaw('DATE(order_date) as date, SUM(total_amount) as total_sales')
    //         ->where('status', '!=', 'cancelled') // ignore cancelled orders
    //         ->whereDate('order_date', '>=', $startDate)
    //         ->groupBy(DB::raw('DATE(order_date)'))
    //         ->orderBy('date')
    //         ->get();

    //     // Format for frontend charting library: labels + data
    //     $formatted = [
    //         'labels' => $sales->pluck('date'),
    //         'data' => $sales->pluck('total_sales'),
    //     ];

    //     return response()->json($formatted);
    // }


    public function salesChart(Request $request)
    {
        $range = $request->query('range', 'month'); // default to 'month'

        $query = DB::table('orders')
            ->where('status', '!=', 'cancelled');

        $now = now();

        switch ($range) {
            case 'day':
                $start = $now->copy()->startOfDay();
                $sales = $query
                    ->whereBetween('order_date', [$start, $now])
                    ->selectRaw("HOUR(order_date) as label, SUM(total_amount) as total_sales")
                    ->groupByRaw("HOUR(order_date)")
                    ->orderBy('label')
                    ->get();
                $labels = $sales->pluck('label')->map(fn($h) => Carbon::createFromTime($h)->format('g A'));
                break;

            case 'week':
                $start = $now->copy()->startOfWeek();
                $sales = $query
                    ->whereBetween('order_date', [$start, $now])
                    ->selectRaw("DAYOFWEEK(order_date) as label, SUM(total_amount) as total_sales")
                    ->groupByRaw("DAYOFWEEK(order_date)")
                    ->orderBy('label')
                    ->get();

                $weekdays = [
                    1 => 'Sun',
                    2 => 'Mon',
                    3 => 'Tue',
                    4 => 'Wed',
                    5 => 'Thu',
                    6 => 'Fri',
                    7 => 'Sat'
                ];

                $labels = $sales->pluck('label')->map(fn($dayNum) => $weekdays[$dayNum]);
                break;


            case 'month':
                $start = $now->copy()->startOfMonth(); 
                $sales = $query
                    ->whereBetween('order_date', [$start, $now])
                    ->selectRaw("DATE(order_date) as label, SUM(total_amount) as total_sales")
                    ->groupByRaw("DATE(order_date)")
                    ->orderBy('label')
                    ->get();

                $labels = $sales->pluck('label')->map(fn($d) => Carbon::parse($d)->format('M j'));
                break;


            case 'year':
                $start = $now->copy()->startOfYear();
                $sales = $query
                    ->whereBetween('order_date', [$start, $now])
                    ->selectRaw("MONTH(order_date) as label, SUM(total_amount) as total_sales")
                    ->groupByRaw("MONTH(order_date)")
                    ->orderBy('label')
                    ->get();

                $months = [
                    1 => 'Jan',
                    2 => 'Feb',
                    3 => 'Mar',
                    4 => 'Apr',
                    5 => 'May',
                    6 => 'Jun',
                    7 => 'Jul',
                    8 => 'Aug',
                    9 => 'Sep',
                    10 => 'Oct',
                    11 => 'Nov',
                    12 => 'Dec'
                ];
                $labels = $sales->pluck('label')->map(fn($m) => $months[$m]);
                break;

            case 'all':
                $sales = $query
                    ->selectRaw("YEAR(order_date) as label, SUM(total_amount) as total_sales")
                    ->groupByRaw("YEAR(order_date)")
                    ->orderBy('label')
                    ->get();

                $labels = $sales->pluck('label')->map(fn($y) => (string) $y);
                break;
        }

        return response()->json([
            'labels' => $labels,
            'data' => $sales->pluck('total_sales'),
        ]);
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