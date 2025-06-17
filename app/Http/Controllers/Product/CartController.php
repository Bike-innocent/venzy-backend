<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // app/Http/Controllers/CartController.php

    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric',
        ]);

        $user = $request->user();

        $existing = CartItem::where('user_id', $user->id)
            ->where('product_variant_id', $validated['product_variant_id'])
            ->where('is_checked_out', false)
            ->first();

        if ($existing) {
            $existing->quantity += $validated['quantity'];
            $existing->save();
        } else {
            $user->cartItems()->create([
                ...$validated,
            ]);
        }

        return response()->json(['message' => 'Added to cart']);
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