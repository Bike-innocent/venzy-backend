<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with([
            'user:id,name,email',
            'address',
            'items.product.images',
            'items.variant'
        ])
            ->latest()
            ->get();

        // Append full image URL to each product image
        foreach ($orders as $order) {
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
        }

        return response()->json($orders);
    }


    public function show($id)
    {
        $order = Order::with([
            'items.product.images',
            'items.variant',
            'address',
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


    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:processing,shipped,delivered',
        ]);

        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return response()->json(['message' => 'Order status updated successfully.', 'order' => $order]);
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
}
