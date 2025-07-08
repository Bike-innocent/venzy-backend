<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'images','variants']);

        // 🔹 Filter by status
        if ($request->status === 'active') {
            $query->where('status', 1);
        } elseif ($request->status === 'draft') {
            $query->where('status', 0);
        }

        // 🔹 Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 🔹 Sorting
        switch ($request->sort) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'latest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // 🔹 Pagination
        $perPage = $request->get('per_page', 10);
        $products = $query->paginate($perPage);

        // 🔹 Format image URLs
        $products->getCollection()->transform(function ($product) {
            $product->images = $product->images->map(function ($img) {
                $img->image_path = url('product-images/' . $img->image_path);
                return $img;
            });
            return $product;
        });

        return response()->json([
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }
}