<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{






















    // public function checkout(Request $request)
    // {
    //     $user = $request->user();

    //     $validated = $request->validate([
    //         'address_id' => 'required|exists:addresses,id',
    //     ]);

    //     // Ensure the address belongs to the authenticated user
    //     $address = \App\Models\Address::where('id', $validated['address_id'])
    //         ->where('user_id', $user->id)
    //         ->first();

    //     if (!$address) {
    //         return response()->json(['error' => 'Unauthorized address'], 403);
    //     }

    //     DB::beginTransaction();

    //     try {
    //         $cartItems = CartItem::with('product', 'variant')
    //             ->where('user_id', $user->id)
    //             ->where('is_checked_out', false)
    //             ->get();

    //         if ($cartItems->isEmpty()) {
    //             return response()->json(['error' => 'Cart is empty'], 400);
    //         }

    //         $total = 0;

    //         foreach ($cartItems as $item) {
    //             $stock = $item->variant ? $item->variant->stock : $item->product->stock;

    //             if ($item->quantity > $stock) {
    //                 throw new \Exception('Item out of stock: ' . $item->product->name);
    //             }

    //             $total += $item->quantity * $item->price;
    //         }

    //         $order = Order::create([
    //             'user_id' => $user->id,
    //             'address_id' => $validated['address_id'],
    //             'order_date' => now(),
    //             'total_amount' => $total,
    //             'status' => 'processing',
    //         ]);

    //         foreach ($cartItems as $item) {
    //             OrderItem::create([
    //                 'order_id' => $order->id,
    //                 'product_id' => $item->product_id,
    //                 'product_variant_id' => $item->product_variant_id,
    //                 'quantity' => $item->quantity,
    //                 'price' => $item->price,
    //             ]);

    //             // Update stock
    //             if ($item->variant) {
    //                 $item->variant->decrement('stock', $item->quantity);
    //             } else {
    //                 $item->product->decrement('stock', $item->quantity);
    //             }

    //             $item->is_checked_out = true;
    //             $item->save();
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Order placed successfully',
    //             'order_id' => $order->id
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }




    public function checkout(Request $request)
{
    $user = $request->user();

    $validated = $request->validate([
        'address_id' => 'required|exists:addresses,id',
    ]);

    $address = \App\Models\Address::where('id', $validated['address_id'])
        ->where('user_id', $user->id)
        ->first();

    if (!$address) {
        return response()->json(['error' => 'Unauthorized address'], 403);
    }

    DB::beginTransaction();

    try {
        $cartItems = CartItem::with('product', 'variant')
            ->where('user_id', $user->id)
            ->where('is_checked_out', false)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        $total = 0;

        foreach ($cartItems as $item) {
            $variant = $item->variant;
            $product = $item->product;

            // Check committed quantity for this variant
            $committed = 0;

            if ($variant) {
                $committed = $variant->orderItems()
                    ->whereHas('order', function ($q) {
                        $q->whereIn('status', ['processing', 'shipped']);
                    })->sum('quantity');

                $available = max(0, $variant->stock - $committed);
            } else {
                $committed = $product->orderItems()
                    ->whereHas('order', function ($q) {
                        $q->whereIn('status', ['processing', 'shipped']);
                    })->sum('quantity');

                $available = max(0, $product->stock - $committed);
            }

            if ($item->quantity > $available) {
                throw new \Exception('Insufficient available stock for: ' . $product->name);
            }

            $total += $item->quantity * $item->price;
        }

        $order = Order::create([
            'user_id' => $user->id,
            'address_id' => $validated['address_id'],
            'order_date' => now(),
            'total_amount' => $total,
            'status' => 'processing',
        ]);

        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ]);

            // ✅ Do NOT decrement stock here
            $item->is_checked_out = true;
            $item->save();
        }

        DB::commit();

        return response()->json([
            'message' => 'Order placed successfully',
            'order_id' => $order->id
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
}



































    


    public function userOrders(Request $request)
    {
        $orders = Order::with(['items.product', 'items.variant', 'address'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($orders);
    }




    


    public function show($id)
    {
        $user = auth()->user();

        $order = Order::with(['items.product.images', 'items.variant', 'address'])
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Format product images with full URLs
        foreach ($order->items as $item) {
            if ($item->product && $item->product->images) {
                $item->product->images = $item->product->images->map(function ($image) {
                    if (!preg_match('/^http(s)?:\/\//', $image->image_path)) {
                        $image->image_path = url('product-images/' . $image->image_path);
                    }
                    return $image;
                });
            }
        }

        return response()->json($order);
    }







public function cancel($id)
{
    $order = Order::where('id', $id)
                  ->where('user_id', auth()->id())
                  ->where('status', 'processing') // User can only cancel before shipment
                  ->firstOrFail();

    $order->status = 'cancelled';
    $order->save();

    return response()->json(['message' => 'Order cancelled successfully.']);
}
















}

























    // public function allOrders()
    // {
    //     $orders = Order::with(['user', 'items.product', 'items.variant', 'address'])
    //         ->latest()
    //         ->get();

    //     return response()->json($orders);
    // }

    // public function orderDetails($id)
    // {
    //     $order = Order::with(['items.product', 'items.variant', 'address'])
    //         ->where('id', $id)
    //         ->first();

    //     if (!$order) {
    //         return response()->json(['error' => 'Order not found'], 404);
    //     }

    //     return response()->json($order);
    // }

    // public function updateOrderStatus(Request $request, $id)
    // {
    //     $validated = $request->validate([
    //         'status' => 'required|string|in:processing,shipped,delivered,cancelled',
    //     ]);

    //     $order = Order::find($id);

    //     if (!$order) {
    //         return response()->json(['error' => 'Order not found'], 404);
    //     }

    //     // Ensure the user is authorized to update this order
    //     if ($order->user_id !== $request->user()->id && !$request->user()->hasRole('admin')) {
    //         return response()->json(['error' => 'Unauthorized'], 403);
    //     }

    //     $order->status = $validated['status'];
    //     $order->save();

    //     return response()->json(['message' => 'Order status updated successfully']);
    // }

    // public function deleteOrder($id)
    // {
    //     $order = Order::find($id);

    //     if (!$order) {
    //         return response()->json(['error' => 'Order not found'], 404);
    //     }

    //     // Ensure the user is authorized to delete this order
    //     if ($order->user_id !== request()->user()->id && !request()->user()->hasRole('admin')) {
    //         return response()->json(['error' => 'Unauthorized'], 403);
    //     }

    //     $order->delete();

    //     return response()->json(['message' => 'Order deleted successfully']);
    // }
    // public function userOrderDetails($id, Request $request)
    // {
    //     $order = Order::with(['items.product', 'items.variant', 'address'])
    //         ->where('id', $id)
    //         ->where('user_id', $request->user()->id)
    //         ->first();

    //     if (!$order) {
    //         return response()->json(['error' => 'Order not found'], 404);
    //     }

    //     return response()->json($order);
    // }