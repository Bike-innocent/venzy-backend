<?php


namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use App\Models\Colour;
use App\Models\Size;
use App\Models\User; // Import the User model
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [

            'name' => $this->faker->word,
            'slug' => Str::random(10),
            'category_id' => \App\Models\Category::factory(),
            'brand_id' => \App\Models\Brand::factory(),
            'description' => $this->faker->paragraph,
            'status' => "1",
            'stock' => "100",
            'average_price' => fake()->randomFloat(2, 20, 1000),
            'compared_at_price' => fake()->randomFloat(2, 20, 1000),
        ];
    }
}