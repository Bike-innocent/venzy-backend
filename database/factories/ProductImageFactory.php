<?php


namespace Database\Factories;

use App\Models\ProductImage;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductImageFactory extends Factory
{
    protected $model = ProductImage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $images = [
            "t-shirt-5.jpg", "t-shirt-6.jpg", "t-shirt-7.jpg", "t-shirt-8.jpg",
            "t-shirt-9.jpg", "t-shirt-10.jpg", "t-shirt-11.jpg", "t-shirt-12.jpg",
            "t-shirt-13.jpg", "t-shirt-14.jpg", "white-1.jpg", "white-2.jpg",
            "white-3.jpg", "white-4.jpg", "white-5.jpg", "white-6.jpg",
            "white-7.jpg", "white-8.jpg", "beige-2.jpg", "beige-3.jpg",
            "black-1.jpg", "black-2.jpg", "black-3.jpg", "black-4.jpg",
            "black-5.jpg", "blue-4.jpg", "blue-5.jpg", "blue-6.jpg",
            "blue-7.jpg", "blue-8.jpg", "brown.jpg", "brown-2.jpg",
            "brown-3.jpg", "brown-4.jpg", "brown-5.jpg", "men-hoodie-5.jpg",
            "men-hoodie-6.jpg", "men-hoodie-7.jpg", "men-hoodie-8.jpg",
            "orange-1.jpg", "paddle-boards-1.jpg",
        ];

        return [
            'product_id' => Product::inRandomOrder()->first()->id,
            'image_path' =>  $images[array_rand($images)],
            'product_variant_id' => null, 
        ];
    }
}