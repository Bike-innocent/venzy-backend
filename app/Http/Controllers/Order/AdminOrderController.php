<?php

namespace App\Http\Controllers\Order;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{




    // public function index(Request $request)
    // {
    //     $orders = Order::with([
    //         'user:id,name,email',
    //         'address',
    //         'items.product.images',
    //         'items.variant'
    //     ])
    //         ->latest()
    //         ->get();

    //     // Append full image URL to each product image
    //     foreach ($orders as $order) {
    //         foreach ($order->items as $item) {
    //             if ($item->product && $item->product->images) {
    //                 $item->product->images->transform(function ($image) {
    //                     if (!preg_match('/^http(s)?:\/\//', $image->image_path)) {
    //                         $image->image_path = url('product-images/' . $image->image_path);
    //                     }
    //                     return $image;
    //                 });
    //             }
    //         }
    //     }

    //     return response()->json($orders);
    // }








    // public function index(Request $request)
    // {
    //     $status = $request->input('status');
    //     $sort = $request->input('sort', 'date_desc');
    //     $page = $request->get('page', 1);
    //     $perPage = 15;

    //     // Fetch all orders first
    //     $orders = Order::with([
    //         'user:id,name,email',
    //         'address',
    //         'items.product.images',
    //         'items.variant'
    //     ])->get();

    //     // Filter
    //     if ($status && $status !== 'all') {
    //         $orders = $orders->where('status', $status);
    //     }

    //     // Sort
    //     switch ($sort) {
    //         case 'date_asc':
    //             $orders = $orders->sortBy('order_date');
    //             break;
    //         case 'date_desc':
    //             $orders = $orders->sortByDesc('order_date');
    //             break;
    //         case 'amount_asc':
    //             $orders = $orders->sortBy('total_amount');
    //             break;
    //         case 'amount_desc':
    //             $orders = $orders->sortByDesc('total_amount');
    //             break;
    //         case 'status_asc':
    //             $orders = $orders->sortBy('status');
    //             break;
    //         case 'status_desc':
    //             $orders = $orders->sortByDesc('status');
    //             break;
    //         default:
    //             $orders = $orders->sortByDesc('order_date');
    //             break;
    //     }

    //     // Format images (you can also do this after pagination if performance is a concern)
    //     $orders = $orders->map(function ($order) {
    //         foreach ($order->items as $item) {
    //             if ($item->product && $item->product->images) {
    //                 $item->product->images->transform(function ($image) {
    //                     if (!preg_match('/^http(s)?:\/\//', $image->image_path)) {
    //                         $image->image_path = url('product-images/' . $image->image_path);
    //                     }
    //                     return $image;
    //                 });
    //             }
    //         }
    //         return $order;
    //     });

    //     // Manual pagination
    //     $paginated = new LengthAwarePaginator(
    //         $orders->forPage($page, $perPage)->values(),
    //         $orders->count(),
    //         $perPage,
    //         $page,
    //         ['path' => url()->current()]
    //     );

    //     return response()->json([
    //         'data' => $paginated->items(),
    //         'meta' => [
    //             'current_page' => $paginated->currentPage(),
    //             'last_page' => $paginated->lastPage(),
    //             'per_page' => $paginated->perPage(),
    //             'total' => $paginated->total(),
    //         ],
    //     ]);
    // }








    public function index(Request $request)
    {
        $status = $request->input('status'); // 'all', 'unfulfilled', 'unpaid', 'fulfilled'
        $sort = $request->input('sort', 'date_desc');
        $page = $request->get('page', 1);
        $perPage = 15;

        // Fetch all orders first
        $orders = Order::with([
            'user:id,name,email',
            'items.product.images',
            'items.variant',
            'fulfillment',

        ])->get();

        // Handle custom tab filters
        if ($status && $status !== 'all') {
            if ($status === 'unfulfilled') {
                $orders = $orders->where('fulfillment_status', 'unfulfilled');
            } elseif ($status === 'unpaid') {
                $orders = $orders->filter(function ($order) {
                    return in_array($order->payment_status, ['unpaid', 'pending']);
                });
            } elseif ($status === 'fulfilled') {
                $orders = $orders->where('fulfillment_status', 'fulfilled');
            } elseif ($status === 'paid') {
                $orders = $orders->where('payment_status', 'paid');
            };
        }

        // Sorting
        switch ($sort) {
            case 'date_asc':
                $orders = $orders->sortBy('order_date');
                break;
            case 'date_desc':
                $orders = $orders->sortByDesc('order_date');
                break;
            case 'amount_asc':
                $orders = $orders->sortBy('total_amount');
                break;
            case 'amount_desc':
                $orders = $orders->sortByDesc('total_amount');
                break;

            default:
                $orders = $orders->sortByDesc('order_date');
                break;
        }

        // Append full image URLs
        $orders = $orders->map(function ($order) {
            foreach ($order->items as $item) {
                if ($item->product && $item->product->images) {
                    $item->product->images->transform(function ($image) {
                        if (!preg_match('/^http(s)?:\/\//', $image->image_path)) {
                            $image->image_path = url('product-images/' . $image->image_path);
                        }
                        return $image;
                    });
                }
            }
            return $order;
        });

        // Paginate manually
        $paginated = new LengthAwarePaginator(
            $orders->forPage($page, $perPage)->values(),
            $orders->count(),
            $perPage,
            $page,
            ['path' => url()->current()]
        );

        return response()->json([
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }






















    public function show($id)
    {
        $order = Order::with([
            'items.product.images',
            'items.variant',
            'fulfillment',
            'user'
        ])->findOrFail($id);

        // Append full image URLs
        foreach ($order->items as $item) {
            if ($item->product && $item->product->images) {
                $item->product->images->map(function ($image) {
                    if (!preg_match('/^http(s)?:\/\//', $image->image_path)) {
                        $image->image_path = url('product-images/' . $image->image_path);
                    }
                    return $image;
                });
            }
        }

        return response()->json($order);
    }













    // public function updateStatus(Request $request, $id)
    // {
    //     $request->validate([
    //         'status' => 'required|in:processing,shipped,delivered',
    //     ]);

    //     $order = Order::findOrFail($id);
    //     $order->status = $request->status;
    //     $order->save();

    //     return response()->json(['message' => 'Order status updated successfully.', 'order' => $order]);
    // }






    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:processing,shipped,delivered',
        ]);

        $order = Order::with('items')->findOrFail($id);
        $previousStatus = $order->status;

        // Only handle stock reduction on transition TO delivered
        if ($request->status === 'delivered' && $previousStatus !== 'delivered') {
            DB::beginTransaction();
            try {
                foreach ($order->items as $item) {
                    if ($item->product_variant_id) {
                        // Variant product
                        $variant = \App\Models\ProductVariant::find($item->product_variant_id);
                        if ($variant) {
                            $variant->stock = max(0, $variant->stock - $item->quantity);
                            $variant->save();
                        }
                    } else {
                        // Simple product
                        $product = \App\Models\Product::find($item->product_id);
                        if ($product) {
                            $product->stock = max(0, $product->stock - $item->quantity);
                            $product->save();
                        }
                    }
                }

                $order->status = $request->status;
                $order->save();

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                return response()->json(['error' => 'Failed to update stock: ' . $e->getMessage()], 500);
            }
        } else {
            // Just update status normally
            $order->status = $request->status;
            $order->save();
        }

        return response()->json([
            'message' => 'Order status updated successfully.',
            'order' => $order,
        ]);
    }
















    public function adminCancel($id)
    {
        $order = Order::findOrFail($id);

        if (in_array($order->status, ['delivered', 'cancelled'])) {
            return response()->json(['message' => 'Cannot cancel a delivered or already cancelled order.'], 400);
        }

        $order->status = 'cancelled';
        $order->save();

        return response()->json(['message' => 'Order cancelled by admin.']);
    }

















    // public function fulfill(Request $request, Order $order)
    // {
    //     if ($order->fulfillment_status !== 'unfulfilled') {
    //         return response()->json(['message' => 'Order already fulfilled or cancelled'], 400);
    //     }

    //     $data = $request->validate([
    //         'carrier_name' => 'nullable|string|max:255',
    //         'tracking_number' => 'nullable|string|max:255',
    //         'tracking_url' => 'nullable|string|max:255',
    //         'notes' => 'nullable|string',
    //     ]);

    //     $fulfillment = $order->fulfillment()->create([
    //         'carrier_name' => $data['carrier_name'],
    //         'tracking_number' => $data['tracking_number'],
    //         'tracking_url' => $data['tracking_url'],
    //         'notes' => $data['notes'],
    //         'dispatched_at' => now(),
    //     ]);

    //     $order->update([
    //         'fulfillment_status' => 'fulfilled',
    //         'delivery_status' => 'in_transit',
    //     ]);

    //     return response()->json([
    //         'message' => 'Fulfillment created',
    //         'fulfillment' => $fulfillment,
    //     ]);
    // }








    public function fulfill(Request $request, Order $order)
    {
        $data = $request->validate([
            'carrier_name' => 'nullable|string|max:255',
            'tracking_number' => 'nullable|string|max:255',
            'tracking_url' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $fulfillment = $order->fulfillment;

        if ($fulfillment) {
            // Update existing fulfillment info
            $fulfillment->update($data);

            return response()->json([
                'message' => 'Fulfillment info updated.',
                'fulfillment' => $fulfillment,
            ]);
        }

        // Only allow fulfillment creation for paid & unfulfilled orders
        if ($order->payment_status !== 'paid') {
            return response()->json([
                'message' => 'Only paid orders can be fulfilled.',
            ], 400);
        }

        if (in_array($order->fulfillment_status, ['fulfilled', 'cancelled', 'returned'])) {
            return response()->json([
                'message' => 'Order has already been fulfilled, cancelled, or returned.',
            ], 400);
        }

        // Create new fulfillment record
        $fulfillment = $order->fulfillment()->create([
            ...$data,
            'dispatched_at' => now(),
        ]);

        $order->update([
            'fulfillment_status' => 'fulfilled',
            'delivery_status' => 'in_transit',
        ]);

        return response()->json([
            'message' => 'Order has been fulfilled and is now in transit.',
            'fulfillment' => $fulfillment,
        ]);
    }




    public function markAsPaid(Request $request, Order $order)
    {
        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'Order is already marked as paid.'], 400);
        }

        $data = $request->validate([
            'method' => 'required|string|max:255',
            'reference' => 'nullable|string|max:255',
        ]);

        $payment = $order->payments()->create([
            'status' => 'paid',
            'method' => $data['method'],
            'reference' => $data['reference'] ?? null,
            'amount' => $order->total_amount,
            'paid_at' => now(),
        ]);

        $order->update([
            'payment_status' => 'paid',
            'payment_method' => $data['method'],
        ]);

        return response()->json([
            'message' => 'Order marked as paid.',
            'payment' => $payment,
        ]);
    }
}