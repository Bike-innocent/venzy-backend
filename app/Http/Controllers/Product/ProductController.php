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

            // Variant values
            'product_variant_values' => 'array',
            // 'product_variant_values.*.product_variant_id' => 'required|integer',
            'product_variant_values.*.comboKey' => 'required|string',

            'product_variant_values.*.variant_option_value_id' => 'required|exists:variant_option_values,id',
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
            $variantMap = $this->syncProductVariants($product, $validated['product_variants'] ?? []);

            // 5. Sync variant values safely (delete existing and reinsert for *this* product variant)
            $this->syncVariantValues($variantMap, $validated['product_variant_values'] ?? []);

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
            foreach ($validated['deleted_images'] as $imgPath) {
                $image = $product->images()->where('image_path', $imgPath)->first();
                if ($image) {
                    $image->delete();
                    $fullPath = public_path('product-images/' . $imgPath);
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                }
            }
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
        // First delete related variant values
        $variantIds = $product->variants()->pluck('id');
        ProductVariantValue::whereIn('product_variant_id', $variantIds)
            ->whereIn('variant_option_value_id', function ($query) use ($toDetach) {
                $query->select('id')
                      ->from('variant_option_values')
                      ->whereIn('variant_option_id', $toDetach);
            })->delete();

        // Now safely delete product variant options
        $product->productVariantOptions()->whereIn('variant_option_id', $toDetach)->delete();
    }

    foreach ($toAttach as $id) {
        $product->productVariantOptions()->create(['variant_option_id' => $id]);
    }
}









    protected function syncProductVariants($product, $newVariants)
    {
        $variantMap = [];
        $existing = $product->variants()->get()->keyBy('combo_key');
        $newComboKeys = [];

        foreach ($newVariants as $variant) {
            $key = $variant['comboKey'];
            $newComboKeys[] = $key;

            if ($existing->has($key)) {
                $existingVariant = $existing[$key];
                $existingVariant->update([
                    'price' => $variant['price'],
                    'stock' => $variant['stock'],
                ]);
                $variantMap[$key] = $existingVariant->id;

                // $existingVariant->values()->delete();
            } else {
                $created = $product->variants()->create([
                    'combo_key' => $key,
                    'price' => $variant['price'],
                    'stock' => $variant['stock'],
                ]);
                $variantMap[$key] = $created->id;
            }
        }

        $product->variants()->whereNotIn('combo_key', $newComboKeys)->get()->each(function ($v) {
            $v->values()->delete();
            $v->delete();
        });

        

        return $variantMap;
    }










protected function syncVariantValues($variantMap, $values)
{
    // Group all values by their comboKey
    $grouped = collect($values)->groupBy('comboKey');

    foreach ($grouped as $comboKey => $valueGroup) {
        if (!isset($variantMap[$comboKey])) {
            throw new \Exception("Invalid variant key: $comboKey");
        }

        $variantId = $variantMap[$comboKey];

        // Delete existing values for this variant
        ProductVariantValue::where('product_variant_id', $variantId)->delete();

        foreach ($valueGroup as $value) {
            $valid = DB::table('variant_option_values')
                ->where('id', $value['variant_option_value_id'])
                ->exists();

            if (!$valid) {
                throw new \Exception("Variant option value ID {$value['variant_option_value_id']} does not exist.");
            }

            ProductVariantValue::create([
                'product_variant_id' => $variantId,
                'variant_option_value_id' => $value['variant_option_value_id'],
            ]);
        }
    }
}



    // protected function syncVariantValues($variantMap, $values)
    // {
    //     foreach ($values as $value) {
    //         $comboKey = $value['comboKey'];

    //         if (!isset($variantMap[$comboKey])) {
    //             throw new \Exception("Invalid variant key: $comboKey");
    //         }

    //         $valid = DB::table('variant_option_values')
    //             ->where('id', $value['variant_option_value_id'])
    //             ->exists();

    //         if (!$valid) {
    //             throw new \Exception("Variant option value ID {$value['variant_option_value_id']} does not exist.");
    //         }

    //         ProductVariantValue::create([
    //             'product_variant_id' => $variantMap[$comboKey],
    //             'variant_option_value_id' => $value['variant_option_value_id'],
    //         ]);
    //     }
    // }
















   // protected function syncProductVariants($product, $newVariants)
    // {
    //     $variantMap = [];
    //     $existing = $product->variants()->get()->keyBy('combo_key');

    //     $newComboKeys = [];

    //     foreach ($newVariants as $variant) {
    //         $key = $variant['comboKey'];
    //         $newComboKeys[] = $key;

    //         if ($existing->has($key)) {
    //             $existingVariant = $existing[$key];
    //             $existingVariant->update([
    //                 'price' => $variant['price'],
    //                 'stock' => $variant['stock'],
    //             ]);
    //             $variantMap[$variant['index']] = $existingVariant->id;

    //             $existingVariant->values()->delete();
    //         } else {
    //             $created = $product->variants()->create([
    //                 'combo_key' => $key,
    //                 'price' => $variant['price'],
    //                 'stock' => $variant['stock'],
    //             ]);
    //             $variantMap[$variant['index']] = $created->id;
    //         }
    //     }

    //     // Delete only product variants that are no longer used
    //     $product->variants()->whereNotIn('combo_key', $newComboKeys)->get()->each(function ($v) {
    //         $v->values()->delete();
    //         $v->delete(); // no force delete
    //     });

    //     return $variantMap;
    // }






    // protected function syncVariantValues($variantMap, $values)
    // {
    //     foreach ($values as $value) {
    //         $variantIndex = $value['product_variant_id'];

    //         if (!isset($variantMap[$variantIndex])) {
    //             throw new \Exception("Invalid variant index: $variantIndex");
    //         }

    //         // ✅ Ensure the variant_option_value_id exists
    //         $valid = DB::table('variant_option_values')->where('id', $value['variant_option_value_id'])->exists();
    //         if (!$valid) {
    //             throw new \Exception("Variant option value ID {$value['variant_option_value_id']} does not exist.");
    //         }

    //         ProductVariantValue::create([
    //             'product_variant_id' => $variantMap[$variantIndex],
    //             'variant_option_value_id' => $value['variant_option_value_id'],
    //         ]);
    //     }
    // }











    // protected function syncVariantValues($variantMap, $values)
    // {
    //     foreach ($values as $value) {
    //         $variantIndex = $value['product_variant_id'];
    //         if (!isset($variantMap[$variantIndex])) {
    //             throw new \Exception("Invalid variant index: $variantIndex");
    //         }

    //         ProductVariantValue::create([
    //             'product_variant_id' => $variantMap[$variantIndex],
    //             'variant_option_value_id' => $value['variant_option_value_id'],
    //         ]);
    //     }
    // }





























    // // Show a single product
    // public function show($slug)
    // {
    //     $product = Product::with([
    //         'category',
    //         'brand',
    //         'images',
    //         'variants.variantValues.variantOptionValue.option'
    //     ])
    //         ->where('slug', $slug)
    //         ->firstOrFail();

    //     // Format image paths
    //     $product->images = $product->images->map(function ($image) {
    //         $image->image_path = url('product-images/' . $image->image_path);
    //         return $image;
    //     });

    //     // Format variant attributes
    //     $product->variants = $product->variants->map(function ($variant) {
    //         $attributes = [];

    //         foreach ($variant->variantValues as $vv) {
    //             $optionValue = $vv->variantOptionValue;
    //             $option = $optionValue?->option;

    //             if ($option && $optionValue) {
    //                 $attributes[$option->name] = $optionValue->value;
    //             }
    //         }

    //         $variant->attributes = $attributes;
    //         unset($variant->variantValues);

    //         return $variant;
    //     });

    //     return response()->json($product);
    // }















    // public function show($slug)
    // {
    //     $product = Product::with([
    //         'category',
    //         'brand',
    //         'images',
    //         'variants.variantValues.variantOptionValue.option'
    //     ])
    //         ->where('slug', $slug)
    //         ->firstOrFail();

    //     // Format image paths
    //     $product->images = $product->images->map(function ($image) {
    //         $image->image_path = url('product-images/' . $image->image_path);
    //         return $image;
    //     });

    //     // Format variant attributes
    //     $product->variants = $product->variants->map(function ($variant) {
    //         $attributes = [];

    //         foreach ($variant->variantValues as $vv) {
    //             $optionValue = $vv->variantOptionValue;
    //             $option = $optionValue?->option;

    //             if ($option && $optionValue) {
    //                 $attributes[$option->name] = $optionValue->value;
    //             }
    //         }

    //         $variant->attributes = $attributes;
    //         unset($variant->variantValues);

    //         return $variant;
    //     });

    //     // Fetch variant options related to this product
    //     $variantOptionIds = $product->productVariantOptions()->pluck('variant_option_id');

    //     $variantOptions = VariantOption::with('values')
    //         ->whereIn('id', $variantOptionIds)
    //         ->get();

    //     // Add variant_options to the response
    //     $product->variant_options = $variantOptions;

    //     return response()->json($product);
    // }









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

        // Get all variant_option_ids linked to this product
        $variantOptionIds = $product->productVariantOptions()->pluck('variant_option_id');

        // Get all used variant_option_value_ids from this product's variants
        $usedValueIds = DB::table('product_variant_values')
            ->join('product_variants', 'product_variant_values.product_variant_id', '=', 'product_variants.id')
            ->where('product_variants.product_id', $product->id)
            ->pluck('variant_option_value_id')
            ->unique();

        // Fetch only options + their used values
        $variantOptions = VariantOption::with(['values' => function ($query) use ($usedValueIds) {
            $query->whereIn('id', $usedValueIds);
        }])
            ->whereIn('id', $variantOptionIds)
            ->get();

        $product->variant_options = $variantOptions;

        return response()->json($product);
    }















    // public function update(Request $request, $slug)
    // {
    //     $product = Product::where('slug', $slug)->firstOrFail();

    //     // Validate the incoming request data
    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'category_id' => 'required|exists:categories,id',
    //         'colour_id' => 'required|exists:colours,id',
    //         'size_id' => 'required|exists:sizes,id',
    //         'description' => 'required|string',
    //         'price' => 'required|numeric',
    //         'stock_quantity' => 'required|integer',
    //         'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
    //         'deleted_images' => 'array', // Array of image IDs to be deleted
    //     ]);

    //     // Update product fields except 'images'
    //     $productData = collect($validated)->except(['images', 'deleted_images',])->toArray();
    //     $product->update($productData);

    //     // Handle deleted images
    //     if ($request->has('deleted_images')) {
    //         $deletedImageIds = $request->input('deleted_images');
    //         $product->images()->whereIn('id', $deletedImageIds)->delete();
    //     }

    //     // Handle new images
    //     if ($request->hasFile('new_images')) {
    //         foreach ($request->file('new_images') as $key => $image) {
    //             // Generate a unique filename with timestamp and original extension
    //             $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

    //             // Move the image to 'public/product-images' folder
    //             $image->move(public_path('product-images'), $filename);

    //             // Save the new image in the database
    //             $newImage = $product->images()->create([
    //                 'image_path' => $filename, // Store only the filename
    //             ]);
    //         }
    //     }

    //     return response()->json(['message' => 'Product updated successfully.']);
    // }



    // Delete a product
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }
}