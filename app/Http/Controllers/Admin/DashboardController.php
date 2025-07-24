<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class DashboardController extends Controller
{

    
protected function getAvailableStock($productId, $variantId = null)
{
    if ($variantId) {
        $variant = ProductVariant::findOrFail($variantId);

        $committed = OrderItem::where('product_variant_id', $variant->id)
            ->whereHas('order', function ($q) {
                $q->where('payment_status', 'paid')
                  ->where('fulfillment_status', 'unfulfilled');
            })
            ->sum('quantity');

        return max(0, $variant->stock - $committed);
    } else {
        $product = Product::findOrFail($productId);

        $committed = OrderItem::where('product_id', $product->id)
            ->whereNull('product_variant_id')
            ->whereHas('order', function ($q) {
                $q->where('payment_status', 'paid')
                  ->where('fulfillment_status', 'unfulfilled');
            })
            ->sum('quantity');

        return max(0, $product->stock - $committed);
    }
}


    public function summary()
    {
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalCustomers = User::count();

        // Only include orders that have been paid (you can add more conditions if needed)
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');

        return response()->json([
            'orders' => $totalOrders,
            'products' => $totalProducts,
            'customers' => $totalCustomers,
            'revenue' => $totalRevenue,
        ]);
    }


















    public function salesChart(Request $request)
    {
        $range = $request->query('range', 'month'); // default to 'month'
        $now = now();

        // Adjusted to exclude only refunded, failed, and unpaid orders
        $query = DB::table('orders')
            ->whereIn('payment_status', ['paid', 'pending']) // include 'pending' and 'paid'
            ->whereNull('deleted_at'); // in case soft deletes are enabled

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

















    public function lowStock(Request $request)
    {
        $range = $request->query('range', '0-5');
        $perPage = $request->query('per_page', 10);
        [$min, $max] = explode('-', $range);
        $min = (int) $min;
        $max = (int) $max;

        $thresholdFilter = fn($available) => $available >= $min && $available <= $max;

        // ✅ Variant products
        $variants = ProductVariant::with(['product.images'])
            ->get()
            ->map(function ($variant) {
                $product = $variant->product;
                $image = optional($product->images->first())->image_path;
                $image = $image ? url('product-images/' . $image) : null;

                $onHand = $variant->stock;
                $available = $this->getAvailableStock($product->id, $variant->id);
                $committed = $onHand - $available;

                return [
                    'type'         => 'variant',
                    'variant_id'   => $variant->id,
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'product_slug' => $product->slug,
                    'combo_key'    => $variant->combo_key,
                    'image'        => $image,
                    'on_hand'      => $onHand,
                    'committed'    => $committed,
                    'available'    => $available,
                ];
            })->filter(fn($item) => $thresholdFilter($item['available']));

        // ✅ Simple products
        $simple = Product::with('images')
            ->whereDoesntHave('variants')
            ->get()
            ->map(function ($product) {
                $image = optional($product->images->first())->image_path;
                $image = $image ? url('product-images/' . $image) : null;

                $onHand = $product->stock;
                $available = $this->getAvailableStock($product->id, null);
                $committed = $onHand - $available;

                return [
                    'type'         => 'simple',
                    'variant_id'   => null,
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'product_slug' => $product->slug,
                    'combo_key'    => '-',
                    'image'        => $image,
                    'on_hand'      => $onHand,
                    'committed'    => $committed,
                    'available'    => $available,
                ];
            })->filter(fn($item) => $thresholdFilter($item['available']));

        // Merge and paginate manually
        $all = $variants->merge($simple)->sortBy('available')->values();
        $page = (int) $request->query('page', 1);
        $offset = ($page - 1) * $perPage;
        $paginated = $all->slice($offset, $perPage)->values();

        return response()->json([
            'data' => $paginated,
            'meta' => [
                'current_page' => $page,
                'per_page'     => (int) $perPage,
                'total'        => $all->count(),
                'last_page'    => ceil($all->count() / $perPage),
            ]
        ]);
    }










    // public function lowStock(Request $request)
    // {
    //     $range = $request->query('range', '0-5'); // default to 0-5
    //     $perPage = $request->query('per_page', 10);

    //     [$min, $max] = explode('-', $range);
    //     $min = (int) $min;
    //     $max = (int) $max;

    //     $thresholdFilter = fn($available) => $available >= $min && $available <= $max;

    //     // ✅ Variant products
    //     $variants = ProductVariant::with(['product.images'])
    //         ->withSum(['orderItems as committed_quantity' => function ($q) {
    //             $q->whereHas('order', function ($q) {
    //                 $q->whereIn('status', ['processing', 'shipped']);
    //             });
    //         }], 'quantity')
    //         ->get()
    //         ->map(function ($variant) {
    //             $product = $variant->product;
    //             $image = optional($product->images->first())->image_path;
    //             $image = $image ? url('product-images/' . $image) : null;

    //             $onHand = $variant->stock;
    //             $committed = $variant->committed_quantity ?? 0;
    //             $available = max(0, $onHand - $committed);

    //             return [
    //                 'type'         => 'variant',
    //                 'variant_id'   => $variant->id,
    //                 'product_id'   => $product->id,
    //                 'product_name' => $product->name,
    //                 'product_slug' => $product->slug,
    //                 'combo_key'    => $variant->combo_key,
    //                 'image'        => $image,
    //                 'on_hand'      => $onHand,
    //                 'committed'    => $committed,
    //                 'available'    => $available,
    //             ];
    //         })->filter(fn($item) => $thresholdFilter($item['available']));

    //     // ✅ Simple products
    //     $simple = Product::with('images')
    //         ->whereDoesntHave('variants')
    //         ->get()
    //         ->map(function ($product) {
    //             $image = optional($product->images->first())->image_path;
    //             $image = $image ? url('product-images/' . $image) : null;

    //             $committed = OrderItem::where('product_id', $product->id)
    //                 ->whereNull('product_variant_id')
    //                 ->whereHas('order', function ($q) {
    //                     $q->whereIn('status', ['processing', 'shipped']);
    //                 })
    //                 ->sum('quantity');

    //             $onHand = $product->stock;
    //             $available = max(0, $onHand - $committed);

    //             return [
    //                 'type'         => 'simple',
    //                 'variant_id'   => null,
    //                 'product_id'   => $product->id,
    //                 'product_name' => $product->name,
    //                 'product_slug' => $product->slug,
    //                 'combo_key'    => '-',
    //                 'image'        => $image,
    //                 'on_hand'      => $onHand,
    //                 'committed'    => $committed,
    //                 'available'    => $available,
    //             ];
    //         })->filter(fn($item) => $thresholdFilter($item['available']));

    //     // Merge and paginate manually
    //     $all = $variants->merge($simple)->sortBy('available')->values();
    //     $page = $request->query('page', 1);
    //     $offset = ($page - 1) * $perPage;
    //     $paginated = $all->slice($offset, $perPage)->values();

    //     return response()->json([
    //         'data' => $paginated,
    //         'meta' => [
    //             'current_page' => (int) $page,
    //             'per_page'     => (int) $perPage,
    //             'total'        => $all->count(),
    //             'last_page'    => ceil($all->count() / $perPage),
    //         ]
    //     ]);
    // }













    public function recentOrders(Request $request)
    {
        $limit = $request->query('limit', 5); // default to 5

        $orders = Order::with(['user', 'address'])
            ->latest('order_date')
            ->limit($limit)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'user' => [
                        'name' => $order->user->name,
                        'email' => $order->user->email,
                    ],
                    'address' => $order->address->state ?? '',
                    'payment_status' => $order->payment_status,
                    'total_amount' => $order->total_amount,
                    'order_date' => \Carbon\Carbon::parse($order->order_date)->format('Y-m-d H:i'),

                ];
            });

        return response()->json($orders);
    }
}