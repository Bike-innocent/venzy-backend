<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller










{

















    public function index()
    {
        return Category::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = Category::create($validated);
        return response()->json($category, 201);
    }

    public function show(Category $productCategory)
    {
        return $productCategory;
    }

    public function update(Request $request, Category $productCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $productCategory->update($validated);
        return response()->json($productCategory);
    }

    public function destroy(Category $productCategory)
    {
        $productCategory->delete();
        return response()->json(null, 204);
    }

    public function restore($id)
    {
        $category = Category::withTrashed()->find($id);

        if ($category) {
            $category->restore();
            return response()->json(['message' => 'Category restored successfully']);
        } else {
            return response()->json(['message' => 'Category not found'], 404);
        }
    }











}
