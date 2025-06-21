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
    public function checkout(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'address_id' => 'required|exists:addresses,id',
        ]);

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
                $stock = $item->variant ? $item->variant->stock : $item->product->stock;

                if ($item->quantity > $stock) {
                    throw new \Exception('Item out of stock: ' . $item->product->name);
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

                // Update stock
                if ($item->variant) {
                    $item->variant->decrement('stock', $item->quantity);
                } else {
                    $item->product->decrement('stock', $item->quantity);
                }

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
}