<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Discount;

class DiscountController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|unique:discounts,code',
            'discount_type' => 'required|in:order,product,shipping',
            //   'value_type' => 'required|in:fixed,percentage',
            'value_type' => 'required_if:discount_type,order,product|in:fixed,percentage',
            'value' => 'required_if:discount_type,order,product|numeric|min:0',


            'requirement_type' => 'required|in:none,min_order_amount,min_quantity',
            'min_order_amount' => 'nullable|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:1',

            'usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        // Nullify unrelated fields

        if ($validated['requirement_type'] !== 'min_order_amount') {
            $validated['min_order_amount'] = null;
        }

        if ($validated['requirement_type'] !== 'min_quantity') {
            $validated['min_quantity'] = null;
        }

        $discount = Discount::create($validated);

        return response()->json(['message' => 'Discount created', 'discount' => $discount], 201);
    }
}