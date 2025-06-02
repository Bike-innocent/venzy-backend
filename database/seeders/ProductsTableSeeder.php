<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantOption;
use App\Models\ProductVariantValue;
use App\Models\VariantOption;
use App\Models\VariantOptionValue;
use Illuminate\Support\Facades\DB;

class ProductsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Seed variant options like "Color" and "Size"
            $colorOption = VariantOption::firstOrCreate(['name' => 'Color']);
            $sizeOption = VariantOption::firstOrCreate(['name' => 'Size']);

            // Seed possible values for Color
            $colors = collect(['Red', 'Blue', 'Green'])->map(function ($color) use ($colorOption) {
                return VariantOptionValue::firstOrCreate([
                    'variant_option_id' => $colorOption->id,
                    'value' => $color,
                ], [
                    'hex_code' => fake()->hexColor,
                ]);
            });

            // Seed possible values for Size
            $sizes = collect(['S', 'M', 'L'])->map(function ($size) use ($sizeOption) {
                return VariantOptionValue::firstOrCreate([
                    'variant_option_id' => $sizeOption->id,
                    'value' => $size,
                ]);
            });

            // Create 10 products
            Product::factory()->count(50)->create()->each(function ($product) use ($colorOption, $sizeOption, $colors, $sizes) {
                // Attach variant options to the product
                ProductVariantOption::firstOrCreate([
                    'product_id' => $product->id,
                    'variant_option_id' => $colorOption->id,
                ]);
                ProductVariantOption::firstOrCreate([
                    'product_id' => $product->id,
                    'variant_option_id' => $sizeOption->id,
                ]);

                // Create all color x size combinations
                foreach ($colors as $color) {
                    foreach ($sizes as $size) {
                        $variant = ProductVariant::create([
                            'product_id' => $product->id,
                            'price' => fake()->randomFloat(2, 20, 1000),
                            'stock_quantity' => fake()->numberBetween(1, 30),
                        ]);

                        // Attach variant values
                        ProductVariantValue::create([
                            'product_variant_id' => $variant->id,
                            'variant_option_value_id' => $color->id,
                        ]);
                        ProductVariantValue::create([
                            'product_variant_id' => $variant->id,
                            'variant_option_value_id' => $size->id,
                        ]);
                    }
                }
            });
        });
    }
}