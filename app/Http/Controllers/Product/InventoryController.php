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
















    public function index(Request $request)
    {
        // 🔹 Base collections
        $variantCollection = ProductVariant::with(['product.images'])
            ->withSum(['orderItems as committed_quantity' => function ($q) {
                // $q->whereHas('order', function ($q) {
                //     $q->whereIn('status', ['processing', 'shipped']);
                // });
                $q->whereHas('order', function ($q) {
                    $q->where('payment_status', 'paid')
                        ->where('fulfillment_status', 'unfulfilled');
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
                    // ->whereHas('order', function ($q) {
                    //     $q->whereIn('status', ['processing', 'shipped']);
                    // })

                    ->whereHas('order', function ($q) {
                        $q->where('payment_status', 'paid')
                            ->where('fulfillment_status', 'unfulfilled');
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
                $inventory = $inventory->sortBy('product_name', SORT_NATURAL | SORT_FLAG_CASE);
                break;
            case 'name_desc':
                $inventory = $inventory->sortByDesc('product_name', SORT_NATURAL | SORT_FLAG_CASE);
                break;
            case 'committed_asc':
                $inventory = $inventory->sortBy('committed');
                break;
            case 'committed_desc':
                $inventory = $inventory->sortByDesc('committed');
                break;
            case 'available_asc':
                $inventory = $inventory->sortBy('available');
                break;
            case 'available_desc':
                $inventory = $inventory->sortByDesc('available');
                break;
            case 'on_hand_asc':
                $inventory = $inventory->sortBy('on_hand');
                break;
            case 'on_hand_desc':
                $inventory = $inventory->sortByDesc('on_hand');
                break;
            case 'oldest':
                $inventory = $inventory->sortBy('created_at');
                break;
            case 'latest':
            default:
                $inventory = $inventory->sortByDesc('created_at');
                break;
        }


        // 🔹 Pagination manually
        $page = $request->get('page', 1);
        $perPage = 50;
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


























    /**     * Update stock for a product or variant.
     *
     * @param Request $request
     * @param string $type 'variant' or 'simple'
     * @param int $id Product or variant ID
     * @return \Illuminate\Http\JsonResponse
     */

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

    /**     * Bulk update stock for multiple products or variants.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function bulkUpdateStock(Request $request)
    {
        $validated = $request->validate([
            'method' => 'required|in:add,set',
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.stock' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $item) {
            $isVariant = ProductVariant::where('id', $item['id'])->exists();

            if ($isVariant) {
                $variant = ProductVariant::findOrFail($item['id']);
                $variant->stock = $validated['method'] === 'add'
                    ? $variant->stock + $item['stock']
                    : $item['stock'];
                $variant->save();
            } else {
                $product = Product::whereDoesntHave('variants')->findOrFail($item['id']);
                $product->stock = $validated['method'] === 'add'
                    ? $product->stock + $item['stock']
                    : $item['stock'];
                $product->save();
            }
        }

        return response()->json(['message' => 'Stock updated successfully.']);
    }
}