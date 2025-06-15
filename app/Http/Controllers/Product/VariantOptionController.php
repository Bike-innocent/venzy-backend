<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;

use App\Models\VariantOption;
use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;

class VariantOptionController extends Controller
{
    public function index(): JsonResponse
    {
        // Get all variant options with their related values
        $variantOptions = VariantOption::with('values')->get();

        return response()->json($variantOptions);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:variant_options,name',
        ]);

        $option = VariantOption::create($validated);
        return response()->json($option, 201);
    }


    public function update(Request $request, VariantOption $variantOption)
    {
        $request->validate([
            'name' => 'required|string|unique:variant_options,name,' . $variantOption->id,
        ]);

        $variantOption->update([
            'name' => $request->name,

        ]);

        return response()->json($variantOption);
    }

    public function getValues($id)
    {
        $option = VariantOption::with('values')->findOrFail($id);
        return response()->json($option->values);
    }
}