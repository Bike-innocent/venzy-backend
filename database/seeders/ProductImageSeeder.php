<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;



class ProductImageSeeder extends Seeder
{
    public function run()
    {
        $products = Product::all();

        foreach ($products as $product) {
            // Generate a random number of images (between 2 and 5)
            $imagesCount = rand(2, 5);
            $images = ProductImage::factory()->count($imagesCount)->make();

            // Set one image as primary
            foreach ($images as $index => $image) {
                $image->product_id = $product->id;
                $image->save();
            }
        }
    }
}
