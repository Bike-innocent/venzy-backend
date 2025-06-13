<?php




namespace App\Http\Controllers\Product;

use App\Models\Product;
use App\Models\ProductImage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ProductVariantValue;
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
            'stock' => 'required|integer',
            'average_price' => 'required|numeric',
            'compared_at_price' => 'nullable|numeric',
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

































































    // // Refresh variant data
    // $product->productVariantOptions()->delete();


    // $product->variants()->withTrashed()->each(function ($variant) {
    //     $variant->values()->delete();
    //     $variant->forceDelete(); // <- actually delete from DB
    // });


    // $variantOptionMap = [];
    // foreach ($validated['product_variant_options'] ?? [] as $option) {
    //     $variantOption = $product->productVariantOptions()->create([
    //         'variant_option_id' => $option['variant_option_id'],
    //     ]);
    //     $variantOptionMap[$option['variant_option_id']] = $variantOption->id;
    // }

    // $variantMap = [];
    // foreach ($validated['product_variants'] ?? [] as $variant) {
    //     $pv = $product->variants()->create([
    //         'combo_key' => $variant['comboKey'],
    //         'price' => $variant['price'],
    //         'stock' => $variant['stock'],
    //     ]);
    //     $variantMap[$variant['index']] = $pv->id;
    // }


    // foreach ($validated['product_variant_values'] ?? [] as $value) {
    //     ProductVariantValue::create([
    //         'product_variant_id' => $variantMap[$value['product_variant_id']],
    //         'variant_option_value_id' => $value['variant_option_value_id'],
    //     ]);
    // }
















    // public function update(Request $request, $slug)
    // {
    //     $validated = $request->validate([
    //         // Product fields
    //         'name' => 'required|string|max:255',
    //         'description' => 'required|string',
    //         'stock' => 'required|integer',
    //         'average_price' => 'required|numeric',
    //         'compared_at_price' => 'nullable|numeric',
    //         'category_id' => 'required|exists:categories,id',
    //         'brand_id' => 'nullable|exists:brands,id',
    //         'status' => 'required|in:0,1',

    //         // Images
    //         'new_images' => 'array',
    //         'new_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
    //         'deleted_images' => 'array',
    //         'deleted_images.*' => 'string',

    //         // Variant options
    //         'product_variant_options' => 'array',
    //         'product_variant_options.*.variant_option_id' => 'required|exists:variant_options,id',

    //         // Variants
    //         'product_variants' => 'array',
    //         'product_variants.*.comboKey' => 'required|string',
    //         'product_variants.*.price' => 'required|numeric',
    //         'product_variants.*.stock' => 'required|integer',
    //         'product_variants.*.index' => 'required|integer',

    //         // Variant values
    //         'product_variant_values' => 'array',
    //         'product_variant_values.*.product_variant_id' => 'required|integer',
    //         'product_variant_values.*.variant_option_value_id' => 'required|exists:variant_option_values,id',
    //     ]);

    //     DB::beginTransaction();

    //     try {
    //         $product = Product::where('slug', $slug)->firstOrFail();

    //         // Update product fields
    //         $product->update([
    //             'name' => $validated['name'],
    //             'description' => $validated['description'],
    //             'category_id' => $validated['category_id'],
    //             'brand_id' => $validated['brand_id'] ?? null,
    //             'stock' => $validated['stock'],
    //             'average_price' => $validated['average_price'],
    //             'compared_at_price' => $validated['compared_at_price'] ?? null,
    //             'status' => $validated['status'],
    //         ]);

    //         // Delete removed images
    //         if (!empty($validated['deleted_images'])) {
    //             foreach ($validated['deleted_images'] as $imgPath) {
    //                 $image = $product->images()->where('image_path', $imgPath)->first();
    //                 if ($image) {
    //                     $image->delete();
    //                     $fullPath = public_path('product-images/' . $imgPath);
    //                     if (file_exists($fullPath)) {
    //                         unlink($fullPath);
    //                     }
    //                 }
    //             }
    //         }

    //         // Add new images
    //         if ($request->hasFile('new_images')) {
    //             foreach ($request->file('new_images') as $image) {
    //                 $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
    //                 $image->move(public_path('product-images'), $filename);

    //                 $product->images()->create([
    //                     'image_path' => $filename,
    //                 ]);
    //             }
    //         }




    //         $existingOptions = $product->productVariantOptions()->pluck('variant_option_id')->toArray();
    //         $newOptions = collect($validated['product_variant_options'] ?? [])->pluck('variant_option_id')->toArray();

    //         // Delete removed options
    //         $toRemove = array_diff($existingOptions, $newOptions);
    //         $product->productVariantOptions()->whereIn('variant_option_id', $toRemove)->delete();

    //         // Add new ones
    //         $toAdd = array_diff($newOptions, $existingOptions);
    //         foreach ($toAdd as $variantOptionId) {
    //             $product->productVariantOptions()->create([
    //                 'variant_option_id' => $variantOptionId,
    //             ]);
    //         }




    //         $existingVariants = $product->variants()->with('values')->get()->keyBy('combo_key');
    //         $newVariants = collect($validated['product_variants'] ?? []);
    //         $variantMap = [];

    //         foreach ($newVariants as $variant) {
    //             if ($existingVariants->has($variant['comboKey'])) {
    //                 $existing = $existingVariants[$variant['comboKey']];
    //                 $existing->update([
    //                     'price' => $variant['price'],
    //                     'stock' => $variant['stock'],
    //                 ]);
    //                 $variantMap[$variant['index']] = $existing->id;

    //                 // Clear old values
    //                 $existing->values()->delete();
    //             } else {
    //                 $newVariant = $product->variants()->create([
    //                     'combo_key' => $variant['comboKey'],
    //                     'price' => $variant['price'],
    //                     'stock' => $variant['stock'],
    //                 ]);
    //                 $variantMap[$variant['index']] = $newVariant->id;
    //             }
    //         }

    //         // Remove variants not present in new request
    //         $comboKeys = $newVariants->pluck('comboKey')->toArray();
    //         $product->variants()->whereNotIn('combo_key', $comboKeys)->each(function ($variant) {
    //             $variant->values()->delete();
    //             $variant->forceDelete();
    //         });









    //         foreach ($validated['product_variant_values'] ?? [] as $value) {
    //             if (!isset($variantMap[$value['product_variant_id']])) {
    //                 throw new \Exception("Invalid variant index: " . $value['product_variant_id']);
    //             }

    //             ProductVariantValue::create([
    //                 'product_variant_id' => $variantMap[$value['product_variant_id']],
    //                 'variant_option_value_id' => $value['variant_option_value_id'],
    //             ]);
    //         }



    //         DB::commit();

    //         return response()->json(['message' => 'Product updated successfully']);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'Failed to update product',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }




















    public function update(Request $request, $slug)
    {
        $validated = $request->validate([
            // Product fields
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'stock' => 'required|integer',
            'average_price' => 'required|numeric',
            'compared_at_price' => 'nullable|numeric',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'status' => 'required|in:0,1',

            // Images
            'new_images' => 'array',
            'new_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'deleted_images' => 'array',
            'deleted_images.*' => 'string',

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
                'stock' => $validated['stock'],
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






    protected function syncProductImages($product, $validated, $request)
    {
        if (!empty($validated['deleted_images'])) {
            $product->images()->whereIn('id', $validated['deleted_images'])->get()->each(function ($image) {
                $fullPath = public_path('product-images/' . $image->image_path);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
                $image->delete();
            });
        }

        if ($request->hasFile('new_images')) {
            foreach ($request->file('new_images') as $image) {
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('product-images'), $filename);
                $product->images()->create(['image_path' => $filename]);
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





















    protected function syncVariants($product, $validated)
    {
        $incomingVariants = collect($validated['product_variants'] ?? []);
        $incomingValues = collect($validated['product_variant_values'] ?? []);
        $incomingKeys = $incomingVariants->pluck('comboKey')->all();

        // Step 1: Map current variants by combo_key for fast lookup
        $existingVariants = $product->variants()->with('variantValues')->get()->keyBy('combo_key');

        $variantMap = [];

        // Step 2: Handle create/update
        foreach ($incomingVariants as $variantData) {
            $comboKey = $variantData['comboKey'];
            $existing = $existingVariants->get($comboKey);

            if ($existing) {
                // Update only if changed to reduce update strain
                if ($existing->price != $variantData['price'] || $existing->stock != $variantData['stock']) {
                    $existing->update([
                        'price' => $variantData['price'],
                        'stock' => $variantData['stock'],
                    ]);
                }

                // Keep track of ID
                $variantMap[$comboKey] = $existing->id;
            } else {
                // Create new variant
                $newVariant = $product->variants()->create([
                    'combo_key' => $comboKey,
                    'price' => $variantData['price'],
                    'stock' => $variantData['stock'],
                ]);
                $variantMap[$comboKey] = $newVariant->id;
            }
        }

        // Step 3: Delete variants that are not present in the incoming list
        $toDelete = $existingVariants->keys()->diff($incomingKeys);

        if ($toDelete->isNotEmpty()) {
            $variantsToDelete = $existingVariants->only($toDelete->all());

            foreach ($variantsToDelete as $variant) {
                // Use direct query to reduce Eloquent overhead
                ProductVariantValue::where('product_variant_id', $variant->id)->delete();
                $variant->delete();
            }
        }

        // Step 4: Re-sync values
        foreach ($variantMap as $comboKey => $variantId) {
            // Avoid unnecessary deletes if not changing
            $existingValues = ProductVariantValue::where('product_variant_id', $variantId)->pluck('variant_option_value_id')->toArray();
            $newValues = $incomingValues->where('comboKey', $comboKey)->pluck('variant_option_value_id')->unique()->values()->toArray();

            if ($existingValues != $newValues) {
                ProductVariantValue::where('product_variant_id', $variantId)->delete();

                $insertData = array_map(function ($valueId) use ($variantId) {
                    return [
                        'product_variant_id' => $variantId,
                        'variant_option_value_id' => $valueId,
                    ];
                }, $newValues);

                ProductVariantValue::insert($insertData);
            }
        }
    }








    // 'variantOptions.values',




    public function show($slug)
    {
        $product = Product::with([
            'category',
            'brand',
            'images',
            'variants.variantValues.variantOptionValue.variantOption',
            // 'productVariantOptions.variantOption',
        ])->where('slug', $slug)->firstOrFail();


        // Format image paths
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
            unset($variant->variantValues); // Optional cleanup
            return $variant;
        });




        // Get variant options with only values used by this product
        $variantOptionIds = $product->productVariantOptions()->pluck('variant_option_id');

        $usedValueIds = DB::table('product_variant_values')
            ->join('product_variants', 'product_variant_values.product_variant_id', '=', 'product_variants.id')
            ->where('product_variants.product_id', $product->id)
            ->pluck('variant_option_value_id')
            ->unique();

        // $variantOptions = \App\Models\VariantOption::with(['values' => function ($query) use ($usedValueIds) {
        //     $query->whereIn('id', $usedValueIds);
        // }])->whereIn('id', $variantOptionIds)->get();

        // $product->variant_options = $variantOptions;

        $variantOptions = \App\Models\VariantOption::with(['values' => function ($query) use ($usedValueIds) {
            $query->whereIn('id', $usedValueIds);
        }])->whereIn('id', $variantOptionIds)->get();

        $product->setRelation('variant_options', $variantOptions); // or $product->variant_options = $variantOptions


        return response()->json($product);
    }




    // Delete a product
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }
}