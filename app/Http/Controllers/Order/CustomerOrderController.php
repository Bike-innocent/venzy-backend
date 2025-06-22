<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\CartItem;
use App\Models\Orderitem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerOrderController extends Controller
{
    // Get all customer orders
    public function index()
    {
        $orders = Order::with(['items.product', 'items.variant', 'address'])->get();
        return response()->json($orders);
    }










    public function store(Request $request)
    {
        // Retrieve authenticated user ID
        $userId = Auth::id();

        // Fetch cart items for the authenticated user
        $cartItems = CartItem::with('product')->where('user_id', $userId)->get();

        // Check if cart is empty
        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'Your cart is empty. Please add products before placing an order.'
            ], 422);
        }

        // Log cart items for debugging
        \Log::info('Cart Items: ', $cartItems->toArray());

        // Prepare order data
        $orderData = [
            'user_id' => $userId,
            'order_date' => now(), // Current date and time
            'total_amount' => 0,
            'status' => 'pending',
        ];

        // Create the order
        $order = Order::create($orderData);

        $totalAmount = 0;

        // Loop through cart items to create order lines and calculate the total amount
        foreach ($cartItems as $cartItem) {
            $order->items()->create([
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
                'price' => $cartItem->product->price, // Assuming 'price' is a column in products table
            ]);

            // Calculate total order amount
            $totalAmount += $cartItem->quantity * $cartItem->product->price;
        }

        // Update the total amount in the order
        $order->update(['total_amount' => $totalAmount]);

        // Clear the user's cart after placing the order
        CartItem::where('user_id', $userId)->delete();

        // Return a success response
        return response()->json([
            'message' => 'Order created successfully!',
            'order' => $order->load('items.product'),
        ]);
    }

































    // View a single order
    public function show($id)
    {
        $order = Order::with('items.product', 'user')->findOrFail($id);
        return response()->json($order);
    }

    // Update an order and its order lines
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,shipped,delivered,cancelled',
            'order_lines' => 'sometimes|array|min:1',
            'order_lines.*.id' => 'nullable|exists:customer_order_lines,id',
            'order_lines.*.product_id' => 'required_with:order_lines.*|exists:products,id',
            'order_lines.*.quantity' => 'required_with:order_lines.*|integer|min:1',
            'order_lines.*.price' => 'required_with:order_lines.*|numeric|min:0',
        ]);

        $order->update(['status' => $validated['status']]);

        $totalAmount = 0;

        // Update or create order lines
        if ($request->has('order_lines')) {
            foreach ($validated['order_lines'] as $line) {
                if (isset($line['id'])) {
                    // Update existing order line
                    $orderLine = Orderitem::find($line['id']);
                    $orderLine->update([
                        'product_id' => $line['product_id'],
                        'quantity' => $line['quantity'],
                        'price' => $line['price'],
                    ]);
                } else {
                    // Create new order line
                    $order->items()->create([
                        'product_id' => $line['product_id'],
                        'quantity' => $line['quantity'],
                        'price' => $line['price'],
                    ]);
                }
                $totalAmount += $line['quantity'] * $line['price'];
            }

            // Update total amount
            $order->update(['total_amount' => $totalAmount]);
        }

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order->load('items.product'),
        ]);
    }

    // Soft delete an order
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }

    // Restore a soft-deleted order
    public function restore($id)
    {
        $order = Order::withTrashed()->findOrFail($id);
        $order->restore();

        return response()->json(['message' => 'Order restored successfully']);
    }
}