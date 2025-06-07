<?php




namespace App\Http\Controllers\Product;

use App\Models\Product;
use App\Models\ProductImage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ProductVariantValue;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{



    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $products = Product::with([
            'category',
            'brand',
            'images',
            'variants.variantValues.variantOptionValue.option'

        ])
            ->paginate($perPage)
            ->through(function ($product) {
                // Attach full image URLs
                $product->images = $product->images->map(function ($image) {
                    $image->image_path = url('product-images/' . $image->image_path);
                    return $image;
                });



                $product->variants = $product->variants->map(function ($variant) {
                    $attributes = [];

                    foreach ($variant->variantValues as $vv) {
                        $optionValue = $vv->variantOptionValue;
                        $option = $optionValue?->option;

                        if ($option && $optionValue) {
                            $attributes[$option->name] = $optionValue->value;
                        }
                    }

                    $variant->attributes = $attributes;
                    unset($variant->variantValues);

                    return $variant;
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




    // public function store(Request $request)
    // {
    //     // Validate the input
    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'category_id' => 'required|exists:categories,id',
    //         'colour_id' => 'required|exists:colours,id',
    //         'size_id' => 'required|exists:sizes,id',
    //         'description' => 'required|string',
    //         'price' => 'required|numeric',
    //         'stock_quantity' => 'required|integer',
    //         'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
    //     ]);

    //     $productData = collect($validated)->except(['images',])->toArray();

    //     // Create the product
    //     $product = Product::create($productData);

    //     // Check if images are uploaded
    //     if ($request->hasFile('images')) {
    //         foreach ($request->file('images') as $key => $image) {
    //             $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
    //             $image->move(public_path('product-images'), $filename);

    //             ProductImage::create([
    //                 'product_id' => $product->id,
    //                 'image_path' => $filename,
    //             ]);
    //         }
    //     }

    //     return response()->json([
    //         'product' => $product,
    //         'message' => 'Product created successfully',
    //     ], 201);
    // }











    // $variantOption = $product->VariantOptions()->create([
    //     'variant_option_id' => $option['variant_option_id'],
    // ]);

























    public function store(Request $request)
    {
        $validated = $request->validate([
            //product table
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'stock' => 'required|integer',
            'average_price' => 'required|integer',
            'compared_at_price' => 'nullable|integer',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'status' => 'required|in:0,1',


            //product_images table
            'images' => 'required|array|min:2',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            //'product_variant_options
            'product_variant_options' => 'array',
            'product_variant_options.*.variant_option_id' => 'required|exists:variant_options,id',
            //product_variants table
            'product_variants' => 'array',
            'product_variants.*.comboKey' => 'required|string',
            'product_variants.*.price' => 'required|numeric',
            'product_variants.*.stock' => 'required|integer',
            'product_variants.*.index' => 'required|integer',

            //product_variant_values table
            'product_variant_values' => 'array',
            'product_variant_values.*.product_variant_id' => 'required|integer',
            'product_variant_values.*.variant_option_value_id' => 'required|exists:variant_option_values,id',
        ]);

        DB::beginTransaction();

        try {


            $product = Product::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']) . '-' . uniqid(), // ensures uniqueness
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'brand_id' => $validated['brand_id'] ?? null,
                'stock' => $validated['stock'],
                'average_price' => $validated['average_price'] ?? null,
                'compared_at_price' => $validated['compared_at_price'] ?? null,
                'status' => $validated['status'],
            ]);


            // Attach images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $key => $image) {
                    $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('product-images'), $filename);

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $filename,
                    ]);
                }
            }

            // Save product variant options
            $variantOptionMap = [];
            // foreach ($validated['product_variant_options'] as $option) {
            foreach ($validated['product_variant_options'] ?? [] as $option) {

                $variantOption = $product->productVariantOptions()->create([
                    'variant_option_id' => $option['variant_option_id'],
                ]);

                $variantOptionMap[$option['variant_option_id']] = $variantOption->id;
            }



            // Save product variants
            $variantMap = [];
            // foreach ($validated['product_variants'] as $variant) {
            foreach ($validated['product_variants'] ?? [] as $variant) {
                $pv = $product->variants()->create([
                    'combo_key' => $variant['comboKey'],
                    'price' => $variant['price'],
                    'stock' => $variant['stock'],
                ]);
                $variantMap[$variant['index']] = $pv->id;
            }



            // foreach ($validated['product_variant_values'] as $value) {
            foreach ($validated['product_variant_values'] ?? [] as $value) {
                ProductVariantValue::create([
                    'product_variant_id' => $variantMap[$value['product_variant_id']],
                    'variant_option_value_id' => $value['variant_option_value_id'],
                ]);
            }


            DB::commit();

            return response()->json(['message' => 'Product created successfully'], 201);
        } catch (\Exception $e) {
            //  \log::error('error on product: ' . $e->getMessage());
            DB::rollBack();
            return response()->json(['message' => 'Failed to create product', 'error' => $e->getMessage()], 500);
        }
    }


























    // Show a single product
    public function show($slug)
    {
        $product = Product::with([
            'category',
            'brand',
            'images',
            'variants.variantValues.variantOptionValue.option'
        ])
            ->where('slug', $slug)
            ->firstOrFail();

        // Format image paths
        $product->images = $product->images->map(function ($image) {
            $image->image_path = url('product-images/' . $image->image_path);
            return $image;
        });

        // Format variant attributes
        $product->variants = $product->variants->map(function ($variant) {
            $attributes = [];

            foreach ($variant->variantValues as $vv) {
                $optionValue = $vv->variantOptionValue;
                $option = $optionValue?->option;

                if ($option && $optionValue) {
                    $attributes[$option->name] = $optionValue->value;
                }
            }

            $variant->attributes = $attributes;
            unset($variant->variantValues);

            return $variant;
        });

        return response()->json($product);
    }














    public function update(Request $request, $slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        // Validate the incoming request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'colour_id' => 'required|exists:colours,id',
            'size_id' => 'required|exists:sizes,id',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'stock_quantity' => 'required|integer',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'deleted_images' => 'array', // Array of image IDs to be deleted
        ]);

        // Update product fields except 'images'
        $productData = collect($validated)->except(['images', 'deleted_images',])->toArray();
        $product->update($productData);

        // Handle deleted images
        if ($request->has('deleted_images')) {
            $deletedImageIds = $request->input('deleted_images');
            $product->images()->whereIn('id', $deletedImageIds)->delete();
        }

        // Handle new images
        if ($request->hasFile('new_images')) {
            foreach ($request->file('new_images') as $key => $image) {
                // Generate a unique filename with timestamp and original extension
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                // Move the image to 'public/product-images' folder
                $image->move(public_path('product-images'), $filename);

                // Save the new image in the database
                $newImage = $product->images()->create([
                    'image_path' => $filename, // Store only the filename
                ]);
            }
        }

        return response()->json(['message' => 'Product updated successfully.']);
    }



    // Delete a product
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }
}