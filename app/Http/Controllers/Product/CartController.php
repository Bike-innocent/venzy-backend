<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class CartController extends Controller
{

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









    public function updateCartItem(Request $request, CartItem $cartItem)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if ($cartItem->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check stock
        if ($cartItem->product_variant_id) {
            $variant = ProductVariant::find($cartItem->product_variant_id);
            if ($validated['quantity'] > $variant->stock) {
                return response()->json(['error' => 'Not enough stock'], 400);
            }
        } else {
            if ($validated['quantity'] > $cartItem->product->stock) {
                return response()->json(['error' => 'Not enough stock'], 400);
            }
        }

        $cartItem->quantity = $validated['quantity'];
        $cartItem->save();

        // Reload updated cart
        $updatedCart = $request->user()->cartItems()
            ->where('is_checked_out', false)
            ->with(['product', 'variant', 'product.images'])
            ->get();

        // Update image paths
        $updatedCart->each(function ($item) {
            $item->product->images = $item->product->images->map(function ($image) {
                if (!preg_match('/^http(s)?:\/\//', $image->image_path)) {
                    $image->image_path = url('product-images/' . $image->image_path);
                }
                return $image;
            });
        });

        return response()->json($updatedCart);
    }










    public function removeCartItem(Request $request, CartItem $cartItem)
    {
        // Ensure the cart item belongs to the authenticated user
        if ($cartItem->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $cartItem->delete();

        // Reload updated cart
        $updatedCart = $request->user()->cartItems()
            ->where('is_checked_out', false)
            ->with(['product', 'variant', 'product.images'])
            ->get();

        // Update image paths
        $updatedCart->each(function ($item) {
            $item->product->images = $item->product->images->map(function ($image) {
                if (!preg_match('/^http(s)?:\/\//', $image->image_path)) {
                    $image->image_path = url('product-images/' . $image->image_path);
                }
                return $image;
            });
        });

        return response()->json($updatedCart);
    }
}






















