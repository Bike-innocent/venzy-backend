<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminProductController extends Controller
{
    // public function index(Request $request)
    // {
    //     $query = Product::with(['category', 'images', 'variants']);

    //     // 🔹 Filter by status
    //     if ($request->status === 'active') {
    //         $query->where('status', 1);
    //     } elseif ($request->status === 'draft') {
    //         $query->where('status', 0);
    //     }

    //     // 🔹 Search by name
    //     if ($request->filled('search')) {
    //         $query->where('name', 'like', '%' . $request->search . '%');
    //     }

    //     // 🔹 Sorting
    //     switch ($request->sort) {
    //         case 'name_asc':
    //             $query->orderBy('name', 'asc');
    //             break;
    //         case 'name_desc':
    //             $query->orderBy('name', 'desc');
    //             break;
    //         case 'latest':
    //         default:
    //             $query->orderBy('created_at', 'desc');
    //             break;
    //     }

    //     // 🔹 Pagination
    //     $perPage = $request->get('per_page', 10);
    //     $products = $query->paginate($perPage);

    //     // 🔹 Format image URLs
    //     $products->getCollection()->transform(function ($product) {
    //         $product->images = $product->images->map(function ($img) {
    //             $img->image_path = url('product-images/' . $img->image_path);
    //             return $img;
    //         });
    //         return $product;
    //     });

    //     return response()->json([
    //         'data' => $products->items(),
    //         'meta' => [
    //             'current_page' => $products->currentPage(),
    //             'last_page' => $products->lastPage(),
    //             'per_page' => $products->perPage(),
    //             'total' => $products->total(),
    //         ],
    //     ]);
    // }




    // public function index(Request $request)
    // {
    //     // $query = Product::with(['category', 'images', 'variants']);


    //     $query = Product::with(['category', 'images','variants'])
    //         ->withCount(['variants as total_inventory' => function ($q) {
    //             $q->select(DB::raw("COALESCE(SUM(stock), 0)"));
    //         }])
    //         ->select('*', DB::raw("COALESCE((
    //     SELECT SUM(stock) FROM product_variants WHERE product_variants.product_id = products.id
    // ), products.stock) as total_inventory"));

    //     // 🔹 Filter by status
    //     if ($request->status === 'active') {
    //         $query->where('status', 1);
    //     } elseif ($request->status === 'draft') {
    //         $query->where('status', 0);
    //     }

    //     // 🔹 Search by name
    //     if ($request->filled('search')) {
    //         $query->where('name', 'like', '%' . $request->search . '%');
    //     }

    //     // 🔹 Sorting
    //     switch ($request->sort) {
    //         case 'name_asc':
    //             $query->orderBy('name', 'asc');
    //             break;
    //         case 'name_desc':
    //             $query->orderBy('name', 'desc');
    //             break;
    //         case 'updated':
    //             $query->orderBy('updated_at', 'desc');
    //             break;
    //         case 'inventory_asc':
    //         case 'inventory_desc':
    //             // We'll sort manually after fetching (not ideal for large sets)
    //             break;


    //         case 'latest':
    //         default:
    //             $query->orderBy('created_at', 'desc');
    //             break;
    //     }

    //     // 🔹 Pagination
    //     $perPage = $request->get('per_page', 15);
    //     $products = $query->paginate($perPage);

    //     // 🔹 Format image URLs
    //     $products->getCollection()->transform(function ($product) {
    //         $product->images = $product->images->map(function ($img) {
    //             $img->image_path = url('product-images/' . $img->image_path);
    //             return $img;
    //         });

    //         // Calculate total inventory (used later for sorting or display)
    //         if ($product->variants->count()) {
    //             $product->total_inventory = $product->variants->sum('stock');
    //         } else {
    //             $product->total_inventory = $product->stock;
    //         }

    //         return $product;
    //     });

    //     // 🔹 Manual inventory sort
    //     // if (in_array($request->sort, ['inventory_asc', 'inventory_desc'])) {
    //     //     $sorted = collect($products->items())->sortBy('total_inventory', SORT_REGULAR, $request->sort === 'inventory_desc');
    //     //     $products->setCollection($sorted->values());
    //     // }


    //     if ($request->sort === 'inventory_asc') {
    //         $query->orderBy('total_inventory', 'asc');
    //     } elseif ($request->sort === 'inventory_desc') {
    //         $query->orderBy('total_inventory', 'desc');
    //     }


    //     return response()->json([
    //         'data' => $products->items(),
    //         'meta' => [
    //             'current_page' => $products->currentPage(),
    //             'last_page' => $products->lastPage(),
    //             'per_page' => $products->perPage(),
    //             'total' => $products->total(),
    //         ],
    //     ]);
    // }












    // public function index(Request $request)
    // {
    //     $query = Product::with(['category', 'images', 'variants'])
    //         ->select('products.*')
    //         ->selectSub(function ($subquery) {
    //             $subquery->from('product_variants')
    //                 ->selectRaw('COALESCE(SUM(stock), 0)')
    //                 ->whereColumn('product_variants.product_id', 'products.id');
    //         }, 'variant_inventory')
    //         ->addSelect(DB::raw('
    //         CASE 
    //             WHEN EXISTS (
    //                 SELECT 1 FROM product_variants WHERE product_variants.product_id = products.id
    //             )
    //             THEN (
    //                 SELECT COALESCE(SUM(stock), 0) FROM product_variants WHERE product_variants.product_id = products.id
    //             )
    //             ELSE products.stock
    //         END as total_inventory
    //     '));

    //     // 🔹 Filter by status
    //     if ($request->status === 'active') {
    //         $query->where('status', 1);
    //     } elseif ($request->status === 'draft') {
    //         $query->where('status', 0);
    //     }

    //     // 🔹 Search by name
    //     if ($request->filled('search')) {
    //         $query->where('name', 'like', '%' . $request->search . '%');
    //     }

    //     // 🔹 Sorting
    //     switch ($request->sort) {
    //         case 'name_asc':
    //             $query->orderBy('name', 'asc');
    //             break;
    //         case 'name_desc':
    //             $query->orderBy('name', 'desc');
    //             break;
    //         case 'updated':
    //             $query->orderBy('updated_at', 'desc');
    //             break;
    //         case 'inventory_asc':
    //             $query->orderBy('total_inventory', 'asc');
    //             break;
    //         case 'inventory_desc':
    //             $query->orderBy('total_inventory', 'desc');
    //             break;
    //         case 'latest':
    //         default:
    //             $query->orderBy('created_at', 'desc');
    //             break;
    //     }

    //     // 🔹 Pagination
    //     $perPage = $request->get('per_page', 15);
    //     $products = $query->paginate($perPage);

    //     // 🔹 Format image URLs
    //     $products->getCollection()->transform(function ($product) {
    //         $product->images = $product->images->map(function ($img) {
    //             $img->image_path = url('product-images/' . $img->image_path);
    //             return $img;
    //         });

    //         return $product;
    //     });

    //     return response()->json([
    //         'data' => $products->items(),
    //         'meta' => [
    //             'current_page' => $products->currentPage(),
    //             'last_page' => $products->lastPage(),
    //             'per_page' => $products->perPage(),
    //             'total' => $products->total(),
    //         ],
    //     ]);
    // }










public function index(Request $request)
{
    $query = Product::with(['category', 'images', 'variants'])
        ->select('products.*')
        ->selectSub(function ($subquery) {
            $subquery->from('product_variants')
                ->selectRaw('COALESCE(SUM(stock), 0)')
                ->whereColumn('product_variants.product_id', 'products.id');
        }, 'variant_inventory')
        ->addSelect(DB::raw('
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM product_variants WHERE product_variants.product_id = products.id
                )
                THEN (
                    SELECT COALESCE(SUM(stock), 0) FROM product_variants WHERE product_variants.product_id = products.id
                )
                ELSE products.stock
            END as total_inventory
        '));

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

    // 🔹 Sorting logic
    switch ($request->sort) {
        case 'name_asc':
            $query->orderBy('name', 'asc');
            break;
        case 'name_desc':
            $query->orderBy('name', 'desc');
            break;
        case 'updated_desc':
            $query->orderBy('updated_at', 'desc');
            break;
        case 'updated_asc':
            $query->orderBy('updated_at', 'asc');
            break;
        case 'inventory_asc':
            $query->orderBy('total_inventory', 'asc');
            break;
        case 'inventory_desc':
            $query->orderBy('total_inventory', 'desc');
            break;
        case 'oldest':
            $query->orderBy('created_at', 'asc');
            break;
        case 'latest':
        default:
            $query->orderBy('created_at', 'desc');
            break;
    }

    // 🔹 Pagination
    $perPage = $request->get('per_page', 15);
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








    public function bulkUpdate(Request $request)
    {
        // ✅ Validate request
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // ✅ Update products
        Product::whereIn('id', $request->ids)->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Products updated successfully',
        ]);
    }




    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id',
        ]);

        Product::whereIn('id', $request->ids)->delete();

        return response()->json(['message' => 'Products deleted successfully']);
    }
}