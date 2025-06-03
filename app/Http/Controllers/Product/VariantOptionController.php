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
}
