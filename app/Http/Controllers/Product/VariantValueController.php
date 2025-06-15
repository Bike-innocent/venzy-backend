<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\VariantOptionValue;
use Illuminate\Http\Request;

class VariantValueController extends Controller
{
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'variant_option_id' => 'required|exists:variant_options,id',
    //         'value' => 'required|string|max:255|unique:variant_option_values,value',
    //         'hex_code' => 'nullable|string|max:7', // Optional, hex format like #FFFFFF
    //     ]);

    //     $value = VariantOptionValue::create([
    //         'variant_option_id' => $request->variant_option_id,
    //         'value' => $request->value,
    //         'hex_code' => $request->hex_code,
    //     ]);

    //     return response()->json($value);
    // }



    public function store(Request $request)
    {
        $request->validate([
            'variant_option_id' => 'required|exists:variant_options,id',
            'value' => 'required|string|max:255|unique:variant_option_values,value',
            'hex_code' => 'nullable|string|max:7', // Optional, hex format like #FFFFFF
        ]);

        $value = VariantOptionValue::create([
            'variant_option_id' => $request->variant_option_id,
            'value' => $request->value,
            'hex_code' => $request->hex_code,
        ]);

        // Fetch all values for this option, newest first
        $values = VariantOptionValue::where('variant_option_id', $request->variant_option_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($values);
    }

    public function update(Request $request, VariantOptionValue $variantOptionValue)
    {
        $request->validate([
            'value' => 'required|string|max:255|unique:variant_option_values,value,' . $variantOptionValue->id,
            'hex_code' => 'nullable|string|max:7',
        ]);

        $variantOptionValue->update([
            'value' => $request->value,
            'hex_code' => $request->hex_code,
        ]);

        return response()->json($variantOptionValue);
    }
}