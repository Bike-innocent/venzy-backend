<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class CartController extends Controller
{





    // public function addToCart(Request $request)
    // {
    //     $validated = $request->validate([
    //         'product_id' => 'required|exists:products,id',
    //         'product_variant_id' => 'nullable|exists:product_variants,id',
    //         'quantity' => 'required|integer|min:1',
    //         'price' => 'required|numeric',
    //     ]);

    //     $user = $this->resolveAuthenticatedUser($request);

    //     $product = Product::findOrFail($validated['product_id']);

    //     if ($validated['product_variant_id']) {
    //         $variant = ProductVariant::findOrFail($validated['product_variant_id']);

    //         if ($variant->stock < $validated['quantity']) {
    //             return response()->json(['error' => 'Not enough stock for selected variant'], 400);
    //         }

    //         $existing = CartItem::where('user_id', $user->id)
    //             ->where('product_id', $product->id)
    //             ->where('product_variant_id', $variant->id)
    //             ->where('is_checked_out', false)
    //             ->first();
    //     } else {
    //         if ($product->stock < $validated['quantity']) {
    //             return response()->json(['error' => 'Not enough stock available'], 400);
    //         }

    //         $existing = CartItem::where('user_id', $user->id)
    //             ->where('product_id', $product->id)
    //             ->whereNull('product_variant_id')
    //             ->where('is_checked_out', false)
    //             ->first();
    //     }

    //     if ($existing) {
    //         $existing->quantity += $validated['quantity'];
    //         $existing->save();
    //     } else {
    //         $user->cartItems()->create([
    //             'product_id' => $validated['product_id'],
    //             'product_variant_id' => $validated['product_variant_id'] ?? null,
    //             'quantity' => $validated['quantity'],
    //             'price' => $validated['price'],
    //         ]);
    //     }

    //     // Calculate total cart count
    //     $totalCartCount = $user->cartItems()->where('is_checked_out', false)->sum('quantity');

    //     return response()->json([
    //         'message' => 'Added to cart',
    //         'totalCartCount' => $totalCartCount
    //     ]);
    // }












    // public function addToCart(Request $request)
    // {
    //     $validated = $request->validate([
    //         'product_id' => 'required|exists:products,id',
    //         'product_variant_id' => 'nullable|exists:product_variants,id',
    //         'quantity' => 'required|integer|min:1',
    //         'price' => 'required|numeric',
    //     ]);

    //     $user = $this->resolveAuthenticatedUser($request);

    //     $guestId = $user ? null : $request->header('X-Guest-Id');

    //     $product = Product::findOrFail($validated['product_id']);

    //     if ($validated['product_variant_id']) {
    //         $variant = ProductVariant::findOrFail($validated['product_variant_id']);

    //         if ($variant->stock < $validated['quantity']) {
    //             return response()->json(['error' => 'Not enough stock for selected variant'], 400);
    //         }

    //         $existing = CartItem::where('product_id', $product->id)
    //             ->where('product_variant_id', $variant->id)
    //             ->where('is_checked_out', false)
    //             ->when($user, fn($q) => $q->where('user_id', $user->id))
    //             ->when(!$user, fn($q) => $q->where('guest_id', $guestId))
    //             ->first();
    //     } else {
    //         if ($product->stock < $validated['quantity']) {
    //             return response()->json(['error' => 'Not enough stock available'], 400);
    //         }

    //         $existing = CartItem::where('product_id', $product->id)
    //             ->whereNull('product_variant_id')
    //             ->where('is_checked_out', false)
    //             ->when($user, fn($q) => $q->where('user_id', $user->id))
    //             ->when(!$user, fn($q) => $q->where('guest_id', $guestId))
    //             ->first();
    //     }

    //     if ($existing) {
    //         $existing->quantity += $validated['quantity'];
    //         $existing->save();
    //     } else {
    //         CartItem::create([
    //             'user_id' => $user?->id,
    //             'guest_id' => $guestId,
    //             'product_id' => $validated['product_id'],
    //             'product_variant_id' => $validated['product_variant_id'] ?? null,
    //             'quantity' => $validated['quantity'],
    //             'price' => $validated['price'],
    //         ]);
    //     }

    //     $totalCartCount = CartItem::where('is_checked_out', false)
    //         ->when($user, fn($q) => $q->where('user_id', $user->id))
    //         ->when(!$user, fn($q) => $q->where('guest_id', $guestId))
    //         ->sum('quantity');

    //     return response()->json([
    //         'message' => 'Added to cart',
    //         'totalCartCount' => $totalCartCount
    //     ]);
    // }





    private function resolveAuthenticatedUser(Request $request)
    {
        $header = $request->header('Authorization');

        if ($header && preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            $token = $matches[1];
            $accessToken = PersonalAccessToken::findToken($token);

            if ($accessToken && $accessToken->tokenable) {
                return $accessToken->tokenable;
            }
        }

        return null;
    }



    protected function getAvailableStock($productId, $variantId = null)
    {
        if ($variantId) {
            $variant = ProductVariant::findOrFail($variantId);
            $committed = OrderItem::where('product_variant_id', $variant->id)
                ->whereHas('order', fn($q) => $q->whereIn('status', ['processing', 'shipped']))
                ->sum('quantity');

            return max(0, $variant->stock - $committed);
        } else {
            $product = Product::findOrFail($productId);
            $committed = OrderItem::where('product_id', $product->id)
                ->whereNull('product_variant_id')
                ->whereHas('order', fn($q) => $q->whereIn('status', ['processing', 'shipped']))
                ->sum('quantity');

            return max(0, $product->stock - $committed);
        }
    }

















    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric',
        ]);

        $user = $this->resolveAuthenticatedUser($request);

        $guestId = $user ? null : $request->header('X-Guest-Id');

        // 🔒 Enforce ownership
        if (!$user && !$guestId) {
            return response()->json(['error' => 'Missing guest identifier'], 400);
        }

        $product = Product::findOrFail($validated['product_id']);

        if ($validated['product_variant_id']) {
            $variant = ProductVariant::findOrFail($validated['product_variant_id']);

            // if ($variant->stock < $validated['quantity']) {
            //     return response()->json(['error' => 'Not enough stock for selected variant'], 400);
            // }


            $available = $this->getAvailableStock($validated['product_id'], $validated['product_variant_id'] ?? null);

            if ($available < $validated['quantity']) {
                return response()->json(['error' => 'Not enough stock available'], 400);
            }

            $existing = CartItem::where('product_id', $product->id)
                ->where('product_variant_id', $variant->id)
                ->where('is_checked_out', false)
                ->when($user, fn($q) => $q->where('user_id', $user->id))
                ->when(!$user, fn($q) => $q->where('guest_id', $guestId))
                ->first();
        } else {
            // if ($product->stock < $validated['quantity']) {
            //     return response()->json(['error' => 'Not enough stock available'], 400);
            // }

            $available = $this->getAvailableStock($validated['product_id'], $validated['product_variant_id'] ?? null);

            if ($available < $validated['quantity']) {
                return response()->json(['error' => 'Not enough stock available'], 400);
            }

            $existing = CartItem::where('product_id', $product->id)
                ->whereNull('product_variant_id')
                ->where('is_checked_out', false)
                ->when($user, fn($q) => $q->where('user_id', $user->id))
                ->when(!$user, fn($q) => $q->where('guest_id', $guestId))
                ->first();
        }

        if ($existing) {
            $existing->quantity += $validated['quantity'];
            $existing->save();
        } else {
            CartItem::create([
                'user_id' => $user ? $user->id : null,
                'guest_id' => !$user ? $guestId : null,
                'product_id' => $validated['product_id'],
                'product_variant_id' => $validated['product_variant_id'] ?? null,
                'quantity' => $validated['quantity'],
                'price' => $validated['price'],
            ]);
        }

        $totalCartCount = CartItem::where('is_checked_out', false)
            ->when($user, fn($q) => $q->where('user_id', $user->id))
            ->when(!$user, fn($q) => $q->where('guest_id', $guestId))
            ->sum('quantity');

        return response()->json([
            'message' => 'Added to cart',
            'totalCartCount' => $totalCartCount
        ]);
    }











    public function getCart(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);

        $guestId = $user ? null : $request->header('X-Guest-Id');

        Log::info('User:', ['id' => $user?->id]);
        Log::info('Guest ID:', ['guest_id' => $guestId]);

        if (!$user && !$guestId) {
            return response()->json(['error' => 'Missing guest identifier'], 400);
        }

        $cartItems = CartItem::with(['product', 'variant', 'product.images'])
            ->where('is_checked_out', false)
            ->when($user, fn($q) => $q->where('user_id', $user->id))
            ->when(!$user, fn($q) => $q->where('guest_id', $guestId))
            ->get();

        $cartItems->each(function ($cartItem) {
            $cartItem->product->images = $cartItem->product->images->map(function ($image) {
                if (!preg_match('/^http(s)?:\/\//', $image->image_path)) {
                    $image->image_path = url('product-images/' . $image->image_path);
                }
                return $image;
            });
        });

        $totalCartCount = CartItem::where('is_checked_out', false)
            ->when($user, fn($q) => $q->where('user_id', $user->id))
            ->when(!$user, fn($q) => $q->where('guest_id', $guestId))
            ->sum('quantity');

        // return response()->json($cartItems);
        return response()->json([
            'message' => 'Added to cart',
            'cartItems' => $cartItems,
            'totalCartCount' => $totalCartCount
        ]);
    }










    public function getCartItemQuantity(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
        ]);


        $user = $this->resolveAuthenticatedUser($request);

        $guestId = $user ? null : $request->header('X-Guest-Id');

        if (!$user && !$guestId) {
            return response()->json(['error' => 'Missing guest identifier'], 400);
        }

        $cartItem = CartItem::where('product_id', $request->product_id)
            ->when(
                $request->product_variant_id,
                fn($q) => $q->where('product_variant_id', $request->product_variant_id),
                fn($q) => $q->whereNull('product_variant_id')
            )
            ->where('is_checked_out', false)
            ->when($user, fn($q) => $q->where('user_id', $user->id))
            ->when(!$user, fn($q) => $q->where('guest_id', $guestId))
            ->first();

        return response()->json([
            'quantity' => $cartItem ? $cartItem->quantity : 0
        ]);
    }




















    public function updateCartItem(Request $request, CartItem $cartItem)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $user = $this->resolveAuthenticatedUser($request);

        $guestId = $user ? null : $request->header('X-Guest-Id');

        if (!$user && !$guestId) {
            return response()->json(['error' => 'Missing guest identifier'], 400);
        }

        // Check if the item belongs to the current user or session
        $isOwner = $user
            ? $cartItem->user_id === $user->id
            : $cartItem->guest_id === $guestId;

        if (!$isOwner) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check stock availability
        if ($cartItem->product_variant_id) {
           

            $available = $this->getAvailableStock($cartItem->product_id, $cartItem->product_variant_id);

            if ($validated['quantity'] > $available) {
                return response()->json(['error' => 'Insufficient available stock'], 400);
            }
        } else {
            $available = $this->getAvailableStock($cartItem->product_id, $cartItem->product_variant_id);

            if ($validated['quantity'] > $available) {
                return response()->json(['error' => 'Insufficient available stock'], 400);
            }
        }


        $cartItem->quantity = $validated['quantity'];
        $cartItem->save();

        // Reload updated cart
        $updatedCart = CartItem::with(['product', 'variant', 'product.images'])
            ->where('is_checked_out', false)
            ->when($user, fn($q) => $q->where('user_id', $user->id))
            ->when(!$user, fn($q) => $q->where('guest_id', $guestId))
            ->get();

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
        $user = $this->resolveAuthenticatedUser($request);

        $guestId = $user ? null : $request->header('X-Guest-Id');
        if (!$user && !$guestId) {
            return response()->json(['error' => 'Missing guest identifier'], 400);
        }

        // Check ownership of the cart item
        $isOwner = $user
            ? $cartItem->user_id === $user->id
            : $cartItem->guest_id === $guestId;

        if (!$isOwner) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $cartItem->delete();

        // Reload updated cart
        $updatedCart = CartItem::with(['product', 'variant', 'product.images'])
            ->where('is_checked_out', false)
            ->when($user, fn($q) => $q->where('user_id', $user->id))
            ->when(!$user, fn($q) => $q->where('guest_id', $guestId))
            ->get();

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

























//     public function mergeGuestCart(Request $request)
// {
//     if (!$request->user()) {
//         return;
//     }

//       $guestId = $user ? null : $request->header('X-Guest-Id');
//     $userId = $request->user()->id;

//     $guestItems = CartItem::where('guest_id', $guestId)
//         ->where('is_checked_out', false)
//         ->get();

//     foreach ($guestItems as $item) {
//         // Check if same item already in user's cart
//         $existing = CartItem::where('user_id', $userId)
//             ->where('product_id', $item->product_id)
//             ->where('product_variant_id', $item->product_variant_id)
//             ->where('is_checked_out', false)
//             ->first();

//         if ($existing) {
//             $existing->quantity += $item->quantity;
//             $existing->save();
//             $item->delete(); // remove guest item
//         } else {
//             $item->user_id = $userId;
//             $item->guest_id = null;
//             $item->save();
//         }
//     }
// }