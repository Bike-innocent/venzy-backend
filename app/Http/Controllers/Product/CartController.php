<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // app/Http/Controllers/CartController.php

    // public function addToCart(Request $request)
    // {
    //     $validated = $request->validate([
    //         'product_id' => 'required|exists:products,id',
    //         'product_variant_id' => 'nullable|exists:product_variants,id',
    //         'quantity' => 'required|integer|min:1',
    //         'price' => 'required|numeric',
    //     ]);

    //     $user = $request->user();

    //     $existing = CartItem::where('user_id', $user->id)
    //         ->where('product_variant_id', $validated['product_variant_id'])
    //         ->where('is_checked_out', false)
    //         ->first();

    //     if ($existing) {
    //         $existing->quantity += $validated['quantity'];
    //         $existing->save();
    //     } else {
    //         $user->cartItems()->create([
    //             ...$validated,
    //         ]);
    //     }

    //     return response()->json(['message' => 'Added to cart']);
    // }



    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric',
        ]);

        $user = $request->user();
        $product = Product::findOrFail($validated['product_id']);

        if ($validated['product_variant_id']) {
            $variant = ProductVariant::findOrFail($validated['product_variant_id']);

            if ($variant->stock < $validated['quantity']) {
                return response()->json(['error' => 'Not enough stock for selected variant'], 400);
            }

            $existing = CartItem::where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->where('product_variant_id', $variant->id)
                ->where('is_checked_out', false)
                ->first();
        } else {
            if ($product->stock < $validated['quantity']) {
                return response()->json(['error' => 'Not enough stock available'], 400);
            }

            $existing = CartItem::where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->whereNull('product_variant_id')
                ->where('is_checked_out', false)
                ->first();
        }

        if ($existing) {
            $existing->quantity += $validated['quantity'];
            $existing->save();
        } else {
            $user->cartItems()->create([
                'product_id' => $validated['product_id'],
                'product_variant_id' => $validated['product_variant_id'] ?? null,
                'quantity' => $validated['quantity'],
                'price' => $validated['price'],
            ]);
        }

        return response()->json(['message' => 'Added to cart']);
    }









    public function removeFromCart(Request $request, $id)
    {
        $user = $request->user();

        $cartItem = CartItem::where('user_id', $user->id)
            ->where('id', $id)
            ->where('is_checked_out', false)
            ->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        $cartItem->delete();

        return response()->json(['message' => 'Removed from cart']);
    }










    public function updateCartItem(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $user = $request->user();

        $cartItem = CartItem::where('user_id', $user->id)
            ->where('id', $id)
            ->where('is_checked_out', false)
            ->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        $cartItem->quantity = $validated['quantity'];
        $cartItem->save();

        return response()->json(['message' => 'Cart item updated']);
    }










    public function getCart(Request $request)
    {
        $user = $request->user();

        $cartItems = $user->cartItems()
            ->where('is_checked_out', false)
            ->with(['product', 'variant', 'product.images',]) // eager load full data
            ->get();

        $cartItems->each(function ($cartItem) {
            $cartItem->product->images = $cartItem->product->images->map(function ($image) {
                // Only prefix if not already a full URL
                if (!preg_match('/^http(s)?:\/\//', $image->image_path)) {
                    $image->image_path = url('product-images/' . $image->image_path);
                }
                return $image;
            });
        });
        return response()->json($cartItems);
    }


    
}
