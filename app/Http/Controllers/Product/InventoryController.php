<?php










namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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








    public function index()
    {
        // ✅ Variant-based products
        $variants = ProductVariant::with(['product.images'])
            ->withSum(['orderItems as committed_quantity' => function ($q) {
                $q->whereHas('order', function ($q) {
                    $q->whereIn('status', ['processing', 'shipped']);
                });
            }], 'quantity')
            ->get()
            ->map(function ($variant) {
                $product = $variant->product;
                if (!$product) {
                    // Skip if product is missing (data integrity issue)
                    return null;
                }
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
                ];
            })
            ->filter(); // Remove nulls

        // ✅ Simple products (no variants)
        $simpleProducts = Product::with('images')
            ->whereDoesntHave('variants')
            ->get()
            ->map(function ($product) {
                $image = optional($product->images->first())->image_path;
                $image = $image ? url('product-images/' . $image) : null;

                $committed = OrderItem::where('product_id', $product->id)
                    ->whereNull('product_variant_id') // ✅ simple product only
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
                    'combo_key'    => '-', // no combo
                    'image'        => $image,
                    'on_hand'      => $onHand,
                    'committed'    => $committed,
                    'available'    => $available,
                ];
            });

        // ✅ Merge both
        $inventory = $variants->merge($simpleProducts);

        return response()->json($inventory->values());
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