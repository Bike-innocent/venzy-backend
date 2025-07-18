<?php










namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InventoryController extends Controller
{
    // public function index()
    // {
    //     // ✅ Variant-based products
    //     $variants = ProductVariant::with(['product.images'])
    //         ->withSum(['orderItems as committed_quantity' => function ($q) {
    //             $q->whereHas('order', function ($q) {
    //                 $q->whereIn('status', ['processing', 'shipped']);
    //             });
    //         }], 'quantity')
    //         ->get()
    //         ->map(function ($variant) {
    //             $product = $variant->product;
    //             $image = optional($product->images->first())->image_path;
    //             $image = $image ? url('product-images/' . $image) : null;

    //             $onHand = $variant->stock;
    //             $committed = $variant->committed_quantity ?? 0;
    //             $available = max(0, $onHand - $committed);

    //             return [
    //                 'type'         => 'variant',
    //                 'variant_id'   => $variant->id,
    //                 'product_id'   => $product->id,
    //                 'product_name' => $product->name,
    //                 'product_slug' => $product->slug,   
    //                 'combo_key'    => $variant->combo_key,
    //                 'image'        => $image,
    //                 'on_hand'      => $onHand,
    //                 'committed'    => $committed,
    //                 'available'    => $available,
    //             ];
    //         });

    //     // ✅ Simple products (no variants)
    //     $simpleProducts = Product::with('images')
    //         ->whereDoesntHave('variants')
    //         ->get()
    //         ->map(function ($product) {
    //             $image = optional($product->images->first())->image_path;
    //             $image = $image ? url('product-images/' . $image) : null;

    //             $committed = OrderItem::where('product_id', $product->id)
    //                 ->whereNull('product_variant_id') // ✅ simple product only
    //                 ->whereHas('order', function ($q) {
    //                     $q->whereIn('status', ['processing', 'shipped']);
    //                 })
    //                 ->sum('quantity');

    //             $onHand = $product->stock;
    //             $available = max(0, $onHand - $committed);

    //             return [
    //                 'type'         => 'simple',
    //                 'variant_id'   => null,
    //                 'product_id'   => $product->id,
    //                 'product_name' => $product->name,
    //                  'product_slug' => $product->slug,   
    //                 'combo_key'    => '-', // no combo
    //                 'image'        => $image,
    //                 'on_hand'      => $onHand,
    //                 'committed'    => $committed,
    //                 'available'    => $available,
    //             ];
    //         });

    //     // ✅ Merge both
    //     $inventory = $variants->merge($simpleProducts);

    //     return response()->json($inventory->values());
    // }








    // public function index()
    // {
    //     // ✅ Variant-based products
    //     $variants = ProductVariant::with(['product.images'])
    //         ->withSum(['orderItems as committed_quantity' => function ($q) {
    //             $q->whereHas('order', function ($q) {
    //                 $q->whereIn('status', ['processing', 'shipped']);
    //             });
    //         }], 'quantity')
    //         ->get()
    //         ->map(function ($variant) {
    //             $product = $variant->product;
    //             if (!$product) {
    //                 // Skip if product is missing (data integrity issue)
    //                 return null;
    //             }
    //             $image = optional($product->images->first())->image_path;
    //             $image = $image ? url('product-images/' . $image) : null;

    //             $onHand = $variant->stock;
    //             $committed = $variant->committed_quantity ?? 0;
    //             $available = max(0, $onHand - $committed);

    //             return [
    //                 'type'         => 'variant',
    //                 'variant_id'   => $variant->id,
    //                 'product_id'   => $product->id,
    //                 'product_name' => $product->name,
    //                 'product_slug' => $product->slug,
    //                 'combo_key'    => $variant->combo_key,
    //                 'image'        => $image,
    //                 'on_hand'      => $onHand,
    //                 'committed'    => $committed,
    //                 'available'    => $available,
    //             ];
    //         })
    //         ->filter(); // Remove nulls

    //     // ✅ Simple products (no variants)
    //     $simpleProducts = Product::with('images')
    //         ->whereDoesntHave('variants')
    //         ->get()
    //         ->map(function ($product) {
    //             $image = optional($product->images->first())->image_path;
    //             $image = $image ? url('product-images/' . $image) : null;

    //             $committed = OrderItem::where('product_id', $product->id)
    //                 ->whereNull('product_variant_id') // ✅ simple product only
    //                 ->whereHas('order', function ($q) {
    //                     $q->whereIn('status', ['processing', 'shipped']);
    //                 })
    //                 ->sum('quantity');

    //             $onHand = $product->stock;
    //             $available = max(0, $onHand - $committed);

    //             return [
    //                 'type'         => 'simple',
    //                 'variant_id'   => null,
    //                 'product_id'   => $product->id,
    //                 'product_name' => $product->name,
    //                 'product_slug' => $product->slug,
    //                 'combo_key'    => '-', // no combo
    //                 'image'        => $image,
    //                 'on_hand'      => $onHand,
    //                 'committed'    => $committed,
    //                 'available'    => $available,
    //             ];
    //         });

    //     // ✅ Merge both
    //     $inventory = $variants->merge($simpleProducts);

    //     return response()->json($inventory->values());
    // }
















    public function index(Request $request)
    {
        // 🔹 Base collections
        $variantCollection = ProductVariant::with(['product.images'])
            ->withSum(['orderItems as committed_quantity' => function ($q) {
                $q->whereHas('order', function ($q) {
                    $q->whereIn('status', ['processing', 'shipped']);
                });
            }], 'quantity')
            ->get()
            ->map(function ($variant) {
                $product = $variant->product;
                if (!$product) return null;

                $image = optional($product->images->first())->image_path;
                $image = $image ? url('product-images/' . $image) : null;

                $onHand = $variant->stock;
                $committed = $variant->committed_quantity ?? 0;
                $available = max(0, $onHand - $committed);

                return [
                    'type'         => 'variant',
                    'variant_id'   => $variant->id,
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'product_slug' => $product->slug,
                    'combo_key'    => $variant->combo_key,
                    'image'        => $image,
                    'on_hand'      => $onHand,
                    'committed'    => $committed,
                    'available'    => $available,
                    'created_at'   => $product->created_at,
                ];
            })
            ->filter();

        $simpleProductCollection = Product::with('images')
            ->whereDoesntHave('variants')
            ->get()
            ->map(function ($product) {
                $image = optional($product->images->first())->image_path;
                $image = $image ? url('product-images/' . $image) : null;

                $committed = OrderItem::where('product_id', $product->id)
                    ->whereNull('product_variant_id')
                    ->whereHas('order', function ($q) {
                        $q->whereIn('status', ['processing', 'shipped']);
                    })
                    ->sum('quantity');

                $onHand = $product->stock;
                $available = max(0, $onHand - $committed);

                return [
                    'type'         => 'simple',
                    'variant_id'   => null,
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'product_slug' => $product->slug,
                    'combo_key'    => '-',
                    'image'        => $image,
                    'on_hand'      => $onHand,
                    'committed'    => $committed,
                    'available'    => $available,
                    'created_at'   => $product->created_at,
                ];
            });

        // 🔹 Merge both collections
        $inventory = $variantCollection->merge($simpleProductCollection);

        // 🔹 Filter by search
        if ($request->filled('search')) {
            $inventory = $inventory->filter(function ($item) use ($request) {
                return stripos($item['product_name'], $request->search) !== false;
            });
        }

        // 🔹 Sort
        switch ($request->sort) {
            case 'name_asc':
                $inventory = $inventory->sortBy('product_name');
                break;
            case 'name_desc':
                $inventory = $inventory->sortByDesc('product_name');
                break;
            case 'latest':
            default:
                $inventory = $inventory->sortByDesc('created_at');
                break;
        }

        // 🔹 Pagination manually
        $page = $request->get('page', 1);
        $perPage = 15;
        $paginated = new LengthAwarePaginator(
            $inventory->forPage($page, $perPage)->values(),
            $inventory->count(),
            $perPage,
            $page,
            ['path' => url()->current()]
        );

        return response()->json([
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }



















    public function updateStock(Request $request, $type, $id)
    {
        $validated = $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        if ($type === 'variant') {
            $variant = ProductVariant::findOrFail($id);
            $variant->stock = $validated['stock'];
            $variant->save();
        } elseif ($type === 'simple') {
            $product = Product::whereDoesntHave('variants')->findOrFail($id);
            $product->stock = $validated['stock'];
            $product->save();
        } else {
            return response()->json(['error' => 'Invalid type'], 400);
        }

        return response()->json(['message' => 'Stock updated successfully.']);
    }
}