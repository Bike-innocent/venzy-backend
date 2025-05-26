<?php




namespace App\Http\Controllers\Product;

use App\Models\Product;
use App\Models\ProductImage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    // Fetch all products

    // public function index()
    // {
    //     $products = Product::with(['category',  'colour', 'size',  'images', ])
    //         ->get()
    //         ->map(function ($product) {

    //             // Set URLs for all images
    //             $product->images = $product->images->map(function ($image) {
    //                 $image->image_path = url('product-images/' . $image->image_path);
    //                 return $image;
    //             });
    //             return $product;
    //         });

    //     return response()->json($products);
    // }



    public function index(Request $request)
    {
        // Default to 10 items per page, allow client to override
        $perPage = $request->get('per_page', 10);

        // Fetch paginated products
        $products = Product::with(['category', 'colour', 'size', 'images'])
            ->paginate($perPage)
            ->through(function ($product) {
                // Map image paths
                $product->images = $product->images->map(function ($image) {
                    $image->image_path = url('product-images/' . $image->image_path);
                    return $image;
                });
                return $product;
            });

        // Return paginated response
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



    public function store(Request $request)
    {
        // Validate the input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'colour_id' => 'required|exists:colours,id',
            'size_id' => 'required|exists:sizes,id',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'stock_quantity' => 'required|integer',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $productData = collect($validated)->except(['images',])->toArray();

        // Create the product
        $product = Product::create($productData);

        // Check if images are uploaded
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

        return response()->json([
            'product' => $product,
            'message' => 'Product created successfully',
        ], 201);
    }
















    // Show a single product
    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->with(['category', 'colour', 'size',  'images'])
            ->firstOrFail();


        // Set the URLs for all additional images
        $product->images = $product->images->map(function ($image) {
            $image->image_path = url('product-images/' . $image->image_path);
            return $image;
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