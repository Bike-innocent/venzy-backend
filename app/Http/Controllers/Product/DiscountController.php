<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use Illuminate\Http\Request;
use App\Models\Discount;
use Illuminate\Validation\Rule;

class DiscountController extends Controller
{


    public function index()
    {
        $discounts = Discount::latest()->get();

        return response()->json($discounts);
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|required_if:discount_method,automatic|max:255',
            'code' => 'nullable|string|unique:discounts,code',
            'discount_method' => 'required|in:code,automatic',
            'discount_type' => 'required|in:order,product,shipping',
            'value_type' => 'required_if:discount_type,order,product|in:fixed,percentage',
            'value' => 'required_if:discount_type,order,product|numeric|min:0',

            'requirement_type' => 'required|in:none,min_purchase_amount,min_quantity',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:1',

            'usage_limit' => 'nullable|integer|min:1',
            'once_per_user' => 'nullable|boolean',

            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);


        // Nullify unrelated fields

        if ($validated['discount_method'] === 'automatic') {
            $validated['code'] = null;
        } else {
            $validated['title'] = null;
        }


        if ($validated['discount_type'] === 'shipping') {
            $validated['value_type'] = null;
            $validated['value'] = null;
        }

        if ($validated['requirement_type'] !== 'min_purchase_amount') {
            $validated['min_purchase_amount'] = null;
        }
        if ($validated['requirement_type'] !== 'min_quantity') {
            $validated['min_quantity'] = null;
        }

        $validated['once_per_user'] = $validated['once_per_user'] ?? false;


        $discount = Discount::create($validated);

        return response()->json(['message' => 'Discount created', 'discount' => $discount], 201);
    }



    public function show($id)
    {
        $discount = Discount::findOrFail($id);
        return response()->json($discount);
    }


    public function update(Request $request, $id)
    {
        $discount = Discount::findOrFail($id);

        $validated = $request->validate([
            'title' => 'nullable|string|required_if:discount_method,automatic|max:255',
            'code' => [
                'nullable',
                'string',
                Rule::unique('discounts')->ignore($discount->id),
            ],
            'discount_method' => 'required|in:code,automatic',
            'discount_type' => 'required|in:order,product,shipping',
            'value_type' => 'required_if:discount_type,order,product|in:fixed,percentage',
            'value' => 'required_if:discount_type,order,product|numeric|min:0',

            'requirement_type' => 'required|in:none,min_purchase_amount,min_quantity',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:1',

            'usage_limit' => 'nullable|integer|min:1',
            'once_per_user' => 'nullable|boolean',

            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        // Clean up unrelated fields
        if ($validated['discount_method'] === 'automatic') {
            $validated['code'] = null;
        } else {
            $validated['title'] = null;
        }

        if ($validated['discount_type'] === 'shipping') {
            $validated['value_type'] = null;
            $validated['value'] = null;
        }

        if ($validated['requirement_type'] !== 'min_purchase_amount') {
            $validated['min_purchase_amount'] = null;
        }
        if ($validated['requirement_type'] !== 'min_quantity') {
            $validated['min_quantity'] = null;
        }
        $validated['once_per_user'] = $validated['once_per_user'] ?? false;


        $discount->update($validated);

        return response()->json([
            'message' => 'Discount updated',
            'discount' => $discount,
        ]);
    }

    public function destroy($id)
    {
        $discount = Discount::findOrFail($id);
        $discount->delete();

        return response()->json(['message' => 'Discount deleted successfully']);
    }














    // public function applyDiscount(Request $request)
    // {
    //     $user = $request->user();

    //     $request->validate([
    //         'code' => 'required|string',
    //     ]);

    //     $discount = Discount::where('code', $request->code)
    //         ->where('discount_method', 'code')
    //         ->where('is_active', true)
    //         ->where(function ($q) {
    //             $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
    //         })
    //         ->where(function ($q) {
    //             $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
    //         })
    //         ->first();

    //     if (!$discount) {
    //         return response()->json(['message' => 'Invalid or expired code'], 404);
    //     }

    //     // 🛒 Fetch the user's current cart
    //     $cartItems = CartItem::where('user_id', $user->id)
    //         ->where('is_checked_out', false)
    //         ->get();

    //     if ($cartItems->isEmpty()) {
    //         return response()->json(['message' => 'Your cart is empty'], 400);
    //     }

    //     // 🧮 Calculate cart totals
    //     $subtotal = $cartItems->sum(fn($item) => $item->quantity * $item->price);
    //     $totalQuantity = $cartItems->sum('quantity');

    //     // ✅ Validate the requirement
    //     $meetsRequirement = match ($discount->requirement_type) {
    //         'none' => true,
    //         'min_purchase_amount' => $subtotal >= $discount->min_purchase_amount,
    //         'min_quantity' => $totalQuantity >= $discount->min_quantity,
    //         default => false,
    //     };

    //     if (!$meetsRequirement) {
    //         $reason = match ($discount->requirement_type) {
    //             'min_purchase_amount' => "Requires minimum purchase of ₦{$discount->min_purchase_amount}",
    //             'min_quantity' => "Requires minimum quantity of {$discount->min_quantity} items",
    //             default => "You don't meet the discount conditions",
    //         };

    //         return response()->json(['message' => $reason], 400);
    //     }

    //     // ⚠️ (Optional) Handle usage limits, user-specific usage tracking, etc.

    //     return response()->json(['discount' => $discount]);
    // }





    public function applyDiscount(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'code' => 'required|string',
        ]);

        $discount = Discount::where('code', $request->code)
            ->where('discount_method', 'code')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->first();

        if (!$discount) {
            return response()->json(['message' => 'Invalid or expired code'], 404);
        }

        // 🛒 Fetch the user's current cart
        $cartItems = CartItem::where('user_id', $user->id)
            ->where('is_checked_out', false)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty'], 400);
        }



        // ✅ Specific check for global usage limit
        if (!is_null($discount->usage_limit) && $discount->used_count >= $discount->usage_limit) {
            return response()->json(['message' => 'This discount has reached its usage limit.'], 400);
        }

        // ✅ Specific check for once-per-user
        if ($discount->once_per_user && $user) {
            $hasUsed = $discount->users()->where('user_id', $user->id)->exists();
            if ($hasUsed) {
                return response()->json(['message' => 'You have already used this discount.'], 400);
            }
        }

        // ✅ Check if cart meets requirements
        if (!$discount->isEligibleForCart($cartItems, $user)) {
            $reason = match ($discount->requirement_type) {
                'min_purchase_amount' => "Requires minimum purchase of ₦{$discount->min_purchase_amount}",
                'min_quantity' => "Requires minimum quantity of {$discount->min_quantity} items",
                default => "You don't meet the discount conditions",
            };

            return response()->json(['message' => $reason], 400);
        }


      

        return response()->json(['discount' => $discount]);
    }










    public function checkAutomaticDiscount(Request $request)
    {
        $user = $request->user();

        $cartItems = CartItem::where('user_id', $user->id)
            ->where('is_checked_out', false)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['discount' => null]);
        }

        $eligible = Discount::where('discount_method', 'automatic')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->get()
            ->filter(fn($discount) => $discount->isEligibleForCart($cartItems, $user)) //helper
            ->sortByDesc(fn($d) => $d->estimatedValue($cartItems)) // You can implement this if needed
            ->first();

        return response()->json(['discount' => $eligible]);
    }
}