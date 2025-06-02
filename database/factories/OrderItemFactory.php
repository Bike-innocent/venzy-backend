<?php

// namespace Database\Factories;

// use App\Models\Order;
// use App\Models\Product;
// use Illuminate\Database\Eloquent\Factories\Factory;

// /**
//  * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
//  */
// class OrderItemFactory extends Factory
// {
//     /**
//      * Define the model's default state.
//      *
//      * @return array<string, mixed>
//      */
//     public function definition(): array
//     {
//         $product = Product::inRandomOrder()->first();

//         return [
//             'order_id' => Order::inRandomOrder()->first()->id,
//             'product_id' => $product->id,
//             'quantity' => $this->faker->numberBetween(1, 10),
//             'price' => $product->price,
     
//         ];
//     }
// }  
    










namespace Database\Factories;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $variant = ProductVariant::inRandomOrder()->first();

        return [
            'order_id' => Order::inRandomOrder()->first()->id,
            'product_id' => $variant->product_id, // ensure it links to the parent product
            'product_variant_id' => $variant->id,
            'quantity' => $this->faker->numberBetween(1, 50),
            'price' => $variant->price, // price at time of order
        ];
    }
}