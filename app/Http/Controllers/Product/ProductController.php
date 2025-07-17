<?php




namespace App\Http\Controllers\Product;

use App\Models\Product;
use App\Models\ProductImage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\ProductVariantValue;
use App\Models\Setting;
use App\Models\VariantOption;
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
            'stock' => 'nullable|integer',
            'average_price' => 'required|numeric',
            'compared_at_price' => 'nullable|numeric',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'status' => 'required|in:0,1',



            //product_images table
            'images' => 'required|array|min:2',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:5048',
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
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'brand_id' => $validated['brand_id'] ?? null,
                'stock' => $validated['stock'] ?? null,
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
            // foreach ($validated['product_variants'] ?? [] as $variant) {
            //     $pv = $product->variants()->create([
            //         'combo_key' => $variant['comboKey'],
            //         'price' => $variant['price'],
            //         'stock' => $variant['stock'],
            //     ]);
            //     $variantMap[$variant['index']] = $pv->id;
            // }

            foreach ($validated['product_variants'] ?? [] as $variant) {
                $price = $variant['price'] ?? 0;

                // If price is missing or zero, fallback to average_price
                if (!$price || floatval($price) === 0.0) {
                    $price = $validated['average_price'];
                }

                $pv = $product->variants()->create([
                    'combo_key' => $variant['comboKey'],
                    'price' => $price,
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

            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product, // include the product object
            ], 201);
        } catch (\Exception $e) {
            //  \log::error('error on product: ' . $e->getMessage());
            DB::rollBack();
            return response()->json(['message' => 'Failed to create product', 'error' => $e->getMessage()], 500);
        }
    }


































































    public function update(Request $request, $slug)
    {
        $validated = $request->validate([
            // Product fields
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'stock' => 'nullable|integer',
            'average_price' => 'required|numeric',
            'compared_at_price' => 'nullable|numeric',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'status' => 'required|in:0,1',

            // Images
            'new_images' => 'array',
            'new_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5048',
            'deleted_images' => 'array',
            'deleted_images.*' => 'string',


            'image_order' => 'array',
            'image_order.*.id' => 'nullable|integer|exists:product_images,id',
            'image_order.*.temp_name' => 'nullable|string',
            'image_order.*.position' => 'required|integer',

            // Variant options
            'product_variant_options' => 'array',
            'product_variant_options.*.variant_option_id' => 'required|exists:variant_options,id',



            // Variants
            'product_variants' => 'array',
            'product_variants.*.comboKey' => 'required|string',
            'product_variants.*.price' => 'required|numeric',
            'product_variants.*.stock' => 'required|integer',
            'product_variants.*.index' => 'required|integer',


            'product_variant_values' => 'nullable|array',
            'product_variant_values.*.comboKey' => 'required_with:product_variant_values|string',
            'product_variant_values.*.variant_option_value_id' => 'required_with:product_variant_values|exists:variant_option_values,id',


        ]);

        DB::beginTransaction();

        try {
            $product = Product::where('slug', $slug)->firstOrFail();

            // 1. Update product fields
            $product->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'brand_id' => $validated['brand_id'] ?? null,
                'stock' => $validated['stock'] ?? null,
                'average_price' => $validated['average_price'],
                'compared_at_price' => $validated['compared_at_price'] ?? null,
                'status' => $validated['status'],
            ]);

            // 2. Manage images safely
            $this->syncProductImages($product, $validated, $request);

            // 3. Sync variant options (only attach/detach — do not delete shared ones)
            $this->syncProductVariantOptions($product, $validated['product_variant_options'] ?? []);

            // 4. Sync product variants without deleting shared data
            $this->syncVariants($product, $validated);

            DB::commit();

            return response()->json(['message' => 'Product updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Update failed', 'error' => $e->getMessage()], 500);
        }
    }






    // protected function syncProductImages($product, $validated, $request)
    // {
    //     if (!empty($validated['deleted_images'])) {
    //         $product->images()->whereIn('id', $validated['deleted_images'])->get()->each(function ($image) {
    //             $fullPath = public_path('product-images/' . $image->image_path);
    //             if (file_exists($fullPath)) {
    //                 unlink($fullPath);
    //             }
    //             $image->delete();
    //         });
    //     }

    //     if ($request->hasFile('new_images')) {
    //         foreach ($request->file('new_images') as $image) {
    //             $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
    //             $image->move(public_path('product-images'), $filename);
    //             $product->images()->create(['image_path' => $filename]);
    //         }
    //     }

    //     if ($request->has('image_order')) {
    //         foreach ($request->input('image_order') as $item) {
    //             if (isset($item['id'])) {
    //                 // Update order for existing image
    //                 $product->images()->where('id', $item['id'])->update([
    //                     'order_column' => $item['position'],
    //                 ]);
    //             } elseif (isset($item['temp_name'])) {
    //                 // Try to find newly uploaded image by filename match
    //                 $matched = $product->images()
    //                     ->where('image_path', 'like', '%' . $item['temp_name'])
    //                     ->orderByDesc('id')
    //                     ->first();

    //                 if ($matched) {
    //                     $matched->update(['order_column' => $item['position']]);
    //                 }
    //             }
    //         }
    //     }
    // }











    protected function syncProductImages($product, $validated, $request)
{
    $newImageMap = [];

    // 1. Delete selected images
    if (!empty($validated['deleted_images'])) {
        $product->images()->whereIn('id', $validated['deleted_images'])->get()->each(function ($image) {
            $fullPath = public_path('product-images/' . $image->image_path);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            $image->delete();
        });
    }

    // 2. Upload new images and track filenames for later mapping
    if ($request->hasFile('new_images')) {
        foreach ($request->file('new_images') as $image) {
            $originalName = $image->getClientOriginalName(); // retain temp name
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('product-images'), $filename);
            $newImage = $product->images()->create(['image_path' => $filename]);

            // Save mapping so we can assign order later
            $newImageMap[$originalName] = $newImage->id;
        }
    }

    // 3. Update image order
    if ($request->has('image_order')) {
        foreach ($request->input('image_order') as $item) {
            if (isset($item['id'])) {
                // Existing image
                $product->images()->where('id', $item['id'])->update([
                    'order_column' => $item['position'],
                ]);
            } elseif (isset($item['temp_name']) && isset($newImageMap[$item['temp_name']])) {
                // Match new uploaded image by original name
                $imageId = $newImageMap[$item['temp_name']];
                $product->images()->where('id', $imageId)->update([
                    'order_column' => $item['position'],
                ]);
            }
        }
    }
}



    protected function syncProductVariantOptions($product, $newOptions)
    {
        $existing = $product->productVariantOptions()->pluck('variant_option_id')->toArray();
        $incoming = collect($newOptions)->pluck('variant_option_id')->toArray();

        $toDetach = array_diff($existing, $incoming);
        $toAttach = array_diff($incoming, $existing);

        if (!empty($toDetach)) {




            $product->productVariantOptions()
                ->whereIn('variant_option_id', $toDetach)
                ->get()
                ->each(function ($pivot) {
                    $pivot->delete(); // ✅ Deletes only pivot/related row, not the referenced value
                });
        }

        foreach ($toAttach as $id) {
            $product->productVariantOptions()->create(['variant_option_id' => $id]);
        }
    }









    protected function syncVariants($product, $validated)
    {
        $incomingVariants = collect($validated['product_variants'] ?? []);
        $incomingValues = collect($validated['product_variant_values'] ?? []);
        $incomingKeys = $incomingVariants->pluck('comboKey')->all();

        $existingVariants = $product->variants()->get();

        // Identify variants to delete
        $existingKeys = $existingVariants->pluck('combo_key')->all();
        $keysToDelete = array_diff($existingKeys, $incomingKeys);
        $variantsToDelete = $existingVariants->filter(function ($variant) use ($keysToDelete) {
            return in_array($variant->combo_key, $keysToDelete);
        });

        $variantIdsToDelete = $variantsToDelete->pluck('id')->all();

        if (!empty($variantIdsToDelete)) {
            // Sanitize IDs
            $ids = implode(',', array_map('intval', $variantIdsToDelete));
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            // Delete related variant values
            DB::statement("DELETE FROM product_variant_values WHERE product_variant_id IN ($ids);");
            // Delete variants
            DB::statement("DELETE FROM product_variants WHERE id IN ($ids);");
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // Upsert variants
        $variantMap = [];
        foreach ($incomingVariants as $variantData) {
            $variant = $product->variants()->updateOrCreate(
                ['combo_key' => $variantData['comboKey']],
                [
                    'price' => $variantData['price'],
                    'stock' => $variantData['stock']
                ]
            );
            $variantMap[$variantData['comboKey']] = $variant->id;
        }

        // Recreate variant values
        foreach ($variantMap as $comboKey => $variantId) {
            // Remove previous values
            ProductVariantValue::where('product_variant_id', $variantId)->delete();

            // Prepare new values
            $valuesForCombo = $incomingValues->where('comboKey', $comboKey);
            $insertData = [];
            foreach ($valuesForCombo as $valueData) {
                $insertData[] = [
                    'product_variant_id' => $variantId,
                    'variant_option_value_id' => $valueData['variant_option_value_id'],
                ];
            }

            if (!empty($insertData)) {
                ProductVariantValue::insert($insertData);
            }
        }
    }





















    // public function show($slug)
    // {
    //     $product = Product::with([
    //         'category',
    //         'brand',
    //         'images',
    //         'variants.variantValues.variantOptionValue.variantOption',
    //         // 'productVariantOptions.variantOption',
    //     ])->where('slug', $slug)->firstOrFail();


    //     // Format image paths
    //     $product->images = $product->images->map(function ($image) {
    //         $image->image_path = url('product-images/' . $image->image_path);
    //         return $image;
    //     });


    //     $product->variants = $product->variants->map(function ($variant) {
    //         $attributes = [];

    //         foreach ($variant->variantValues as $vv) {
    //             $optionValue = $vv->variantOptionValue;
    //             $option = $optionValue?->option;

    //             if ($option && $optionValue) {
    //                 $attributes[$option->name] = $optionValue->value;
    //             }
    //         }

    //         // Calculate committed quantity for this variant
    //         $committed = OrderItem::where('product_variant_id', $variant->id)
    //             ->whereHas('order', function ($q) {
    //                 $q->whereIn('status', ['processing', 'shipped']);
    //             })
    //             ->sum('quantity');

    //         $onHand = $variant->stock;
    //         $available = max(0, $onHand - $committed);

    //         $variant->attributes = $attributes;
    //         $variant->available_stock = $available; // ✅ Append to API response
    //         unset($variant->variantValues);

    //         return $variant;
    //     });








    //     // Get variant options with only values used by this product
    //     $variantOptionIds = $product->productVariantOptions()->pluck('variant_option_id');

    //     $usedValueIds = DB::table('product_variant_values')
    //         ->join('product_variants', 'product_variant_values.product_variant_id', '=', 'product_variants.id')
    //         ->where('product_variants.product_id', $product->id)
    //         ->pluck('variant_option_value_id')
    //         ->unique();



    //     $variantOptions = \App\Models\VariantOption::with(['values' => function ($query) use ($usedValueIds) {
    //         $query->whereIn('id', $usedValueIds);
    //     }])->whereIn('id', $variantOptionIds)->get();

    //     $product->setRelation('variant_options', $variantOptions); // or $product->variant_options = $variantOptions


    //     return response()->json($product);
    // }





























    public function show($slug)
    {
        $product = Product::with([
            'category',
            'brand',
            'images',
            'variants.variantValues.variantOptionValue.variantOption',
        ])->where('slug', $slug)->firstOrFail();

        // Format image paths
        $product->images = $product->images->map(function ($image) {
            $image->image_path = url('product-images/' . $image->image_path);
            return $image;
        });

        // Handle variants
        $product->variants = $product->variants->map(function ($variant) {
            $attributes = [];

            foreach ($variant->variantValues as $vv) {
                $optionValue = $vv->variantOptionValue;
                $option = $optionValue?->option;

                if ($option && $optionValue) {
                    $attributes[$option->name] = $optionValue->value;
                }
            }

            // Calculate committed quantity for this variant
            $committed = OrderItem::where('product_variant_id', $variant->id)
                ->whereHas('order', function ($q) {
                    $q->whereIn('status', ['processing', 'shipped']);
                })
                ->sum('quantity');

            $onHand = $variant->stock;
            $available = max(0, $onHand - $committed);

            $variant->attributes = $attributes;
            $variant->available_stock = $available;
            unset($variant->variantValues);

            return $variant;
        });

        // ✅ If no variants, calculate available_stock for the simple product
        if ($product->variants->isEmpty()) {
            $committed = OrderItem::where('product_id', $product->id)
                ->whereNull('product_variant_id') // Only non-variant orders
                ->whereHas('order', function ($q) {
                    $q->whereIn('status', ['processing', 'shipped']);
                })
                ->sum('quantity');

            $onHand = $product->stock;
            $available = max(0, $onHand - $committed);
            $product->available_stock = $available; // ✅ Add to response
        }

        // Get variant options used by this product
        $variantOptionIds = $product->productVariantOptions()->pluck('variant_option_id');

        $usedValueIds = DB::table('product_variant_values')
            ->join('product_variants', 'product_variant_values.product_variant_id', '=', 'product_variants.id')
            ->where('product_variants.product_id', $product->id)
            ->pluck('variant_option_value_id')
            ->unique();

        $variantOptions = \App\Models\VariantOption::with(['values' => function ($query) use ($usedValueIds) {
            $query->whereIn('id', $usedValueIds);
        }])->whereIn('id', $variantOptionIds)->get();

        $product->setRelation('variant_options', $variantOptions);



        return response()->json($product);
    }






    // Delete a product
    // public function destroy($slug)
    // {
    //     $product = Product::where('slug', $slug)->firstOrFail();

    //     // If you want to delete related variants/images etc. do it here
    //     $product->variants()->delete();
    //     $product->productVariantOptions()->delete();
    //     $product->images()->delete(); // If applicable

    //     $product->delete();

    //     return response()->json(['message' => 'Product deleted successfully.']);
    // }



    public function destroy($slug)
    {
        // Find the product
        $product = Product::where('slug', $slug)->firstOrFail();

        // Wrap in a transaction to ensure all deletions happen atomically
        DB::beginTransaction();

        try {
            // Delete related variants
            DB::statement('DELETE FROM product_variants WHERE product_id = ?', [$product->id]);

            // Delete variant options
            DB::statement('DELETE FROM product_variant_options WHERE product_id = ?', [$product->id]);

            // Delete images
            DB::statement('DELETE FROM product_images WHERE product_id = ?', [$product->id]);

            // Delete the product itself
            DB::statement('DELETE FROM products WHERE id = ?', [$product->id]);

            DB::commit();

            return response()->json(['message' => 'Product deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            // Log error or handle as needed
            return response()->json(['error' => 'Failed to delete product.'], 500);
        }
    }
}



























































    // protected function syncVariants($product, $validated)
    // {
    //     $incomingVariants = collect($validated['product_variants'] ?? []);
    //     $incomingValues = collect($validated['product_variant_values'] ?? []); // ✅ Prevent undefined key error
    //     $incomingKeys = $incomingVariants->pluck('comboKey')->all();

    //     // Step 1: Delete removed variants & their values
    //     $existingVariants = $product->variants()->get();
    //     foreach ($existingVariants as $variant) {
    //         if (!in_array($variant->combo_key, $incomingKeys)) {
    //             $variant->variantValues()->delete();
    //             $variant->delete();

    //         }
    //     }

    //     // Step 2: Upsert variants and track their IDs
    //     $variantMap = [];
    //     foreach ($incomingVariants as $variantData) {
    //         $variant = $product->variants()->updateOrCreate(
    //             ['combo_key' => $variantData['comboKey']],
    //             [
    //                 'price' => $variantData['price'],
    //                 'stock' => $variantData['stock']
    //             ]
    //         );
    //         $variantMap[$variantData['comboKey']] = $variant->id;
    //     }

    //     // Step 3: Clear and re-insert variant values for each combo
    //     foreach ($variantMap as $comboKey => $variantId) {
    //         ProductVariantValue::where('product_variant_id', $variantId)->delete();

    //         $valuesForCombo = $incomingValues->where('comboKey', $comboKey);
    //         foreach ($valuesForCombo as $valueData) {
    //             ProductVariantValue::create([
    //                 'product_variant_id' => $variantId,
    //                 'variant_option_value_id' => $valueData['variant_option_value_id'],
    //             ]);
    //         }
    //     }
    // }