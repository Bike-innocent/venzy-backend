<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // View Cart Items
    public function index()
    {
        $cartItems = CartItem::with([
            'product.primaryImage', // Load the primary image
            'product.colour',       // Load the colour relationship
            'product.size'          // Load the size relationship
        ])->where('user_id', Auth::id())->get();

        // Map the cart items with the correct image path and details
        $cartItems = $cartItems->map(function ($item) {
            // Ensure the image path is a full URL
            $imageUrl = null;
            if ($item->product && $item->product->primaryImage) {
                $imageUrl = url('product-images/' . $item->product->primaryImage->image_path);
            }

            return [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'price' => $item->product->price ?? 0,
                'name' => $item->product->name ?? 'Unknown Product',
                'slug' => $item->product->slug ?? null, // Ensure slug is included
                'image' => $imageUrl, // Full URL for the image
                'color' => $item->product->colour->name ?? null,
                'size' => $item->product->size->name ?? null,
            ];

        });

        return response()->json($cartItems);
    }


    // Add to Cart
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = CartItem::updateOrCreate(
            ['user_id' => Auth::id(), 'product_id' => $request->product_id],
            ['quantity' => \DB::raw('quantity + '.$request->quantity)]
        );

        return response()->json(['message' => 'Product added to cart!', 'data' => $cartItem]);
    }

    // Update Cart Item Quantity
    public function update(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        $cartItem = CartItem::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json(['message' => 'Cart updated successfully!', 'data' => $cartItem]);
    }

    // Remove Item from Cart
    public function destroy($id)
    {
        CartItem::where('id', $id)->where('user_id', Auth::id())->delete();
        return response()->json(['message' => 'Item removed from cart!']);
    }
}
