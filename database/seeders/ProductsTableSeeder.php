<?php



// namespace Database\Seeders;

// use Illuminate\Database\Seeder;
// use App\Models\Product;
// use App\Models\ProductVariant;
// use App\Models\ProductVariantOption;
// use App\Models\ProductVariantValue;
// use App\Models\VariantOption;
// use App\Models\VariantOptionValue;
// use Illuminate\Support\Facades\DB;

// class ProductsTableSeeder extends Seeder
// {
//     public function run(): void
//     {
//         DB::transaction(function () {
//             // Seed variant options like "Color" and "Size"
//             $colorOption = VariantOption::firstOrCreate(['name' => 'Color']);
//             $sizeOption = VariantOption::firstOrCreate(['name' => 'Size']);

//             // Seed possible values for Color
//             $colors = collect(['Red', 'Blue', 'Green'])->map(function ($color) use ($colorOption) {
//                 return VariantOptionValue::firstOrCreate([
//                     'variant_option_id' => $colorOption->id,
//                     'value' => $color,
//                 ], [
//                     'hex_code' => fake()->hexColor,
//                 ]);
//             });

//             // Seed possible values for Size
//             $sizes = collect(['S', 'M', 'L'])->map(function ($size) use ($sizeOption) {
//                 return VariantOptionValue::firstOrCreate([
//                     'variant_option_id' => $sizeOption->id,
//                     'value' => $size,
//                 ]);
//             });

//             // Create 10 products
//             Product::factory()->count(50)->create()->each(function ($product) use ($colorOption, $sizeOption, $colors, $sizes) {
//                 // Attach variant options to the product
//                 ProductVariantOption::firstOrCreate([
//                     'product_id' => $product->id,
//                     'variant_option_id' => $colorOption->id,
//                 ]);
//                 ProductVariantOption::firstOrCreate([
//                     'product_id' => $product->id,
//                     'variant_option_id' => $sizeOption->id,
//                 ]);

//                 // Create all color x size combinations
//                 foreach ($colors as $color) {
//                     foreach ($sizes as $size) {
//                         $variant = ProductVariant::create([
//                             'product_id' => $product->id,
//                             'price' => fake()->randomFloat(2, 20, 1000),
//                             'stock_quantity' => fake()->numberBetween(1, 30),
//                         ]);


//                         // Attach variant values
//                         ProductVariantValue::create([
//                             'product_variant_id' => $variant->id,
//                             'variant_option_value_id' => $color->id,
//                         ]);
//                         ProductVariantValue::create([
//                             'product_variant_id' => $variant->id,
//                             'variant_option_value_id' => $size->id,
//                         ]);
//                     }
//                 }
//             });
//         });
//     }
// }






// namespace Database\Seeders;

// use Illuminate\Database\Seeder;
// use App\Models\Product;
// use App\Models\ProductVariant;
// use App\Models\ProductVariantOption;
// use App\Models\ProductVariantValue;
// use App\Models\VariantOption;
// use App\Models\VariantOptionValue;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Str;

// class ProductsTableSeeder extends Seeder
// {
//     public function run(): void
//     {
//         DB::transaction(function () {
//             // Seed variant options like "Color" and "Size"
//             $colorOption = VariantOption::firstOrCreate(['name' => 'Color']);
//             $sizeOption = VariantOption::firstOrCreate(['name' => 'Size']);

//             // Seed possible values for Color
//             $colors = collect(['Red', 'Blue', 'Green'])->map(function ($color) use ($colorOption) {
//                 return VariantOptionValue::firstOrCreate([
//                     'variant_option_id' => $colorOption->id,
//                     'value' => $color,
//                 ], [
//                     'hex_code' => fake()->hexColor(),
//                 ]);
//             });

//             // Seed possible values for Size
//             $sizes = collect(['S', 'M', 'L'])->map(function ($size) use ($sizeOption) {
//                 return VariantOptionValue::firstOrCreate([
//                     'variant_option_id' => $sizeOption->id,
//                     'value' => $size,
//                 ]);
//             });

//             // Create 50 products
//             Product::factory()->count(50)->create()->each(function ($product) use ($colorOption, $sizeOption, $colors, $sizes) {
//                 // Attach variant options to the product
//                 ProductVariantOption::firstOrCreate([
//                     'product_id' => $product->id,
//                     'variant_option_id' => $colorOption->id,
//                 ]);
//                 ProductVariantOption::firstOrCreate([
//                     'product_id' => $product->id,
//                     'variant_option_id' => $sizeOption->id,
//                 ]);

//                 $prices = [];

//                 // Create all color x size combinations
//                 foreach ($colors as $color) {
//                     foreach ($sizes as $size) {
//                         $price = fake()->randomFloat(2, 20, 1000);
//                         $comboKey = "{$color->value} - {$size->value}";

//                         $variant = ProductVariant::create([
//                             'product_id' => $product->id,
//                             'price' => $price,
//                             'stock_quantity' => fake()->numberBetween(1, 30),
//                             'combo_key' => $comboKey,
//                         ]);

//                         // Attach variant values
//                         ProductVariantValue::create([
//                             'product_variant_id' => $variant->id,
//                             'variant_option_value_id' => $color->id,
//                         ]);
//                         ProductVariantValue::create([
//                             'product_variant_id' => $variant->id,
//                             'variant_option_value_id' => $size->id,
//                         ]);

//                         $prices[] = $price;
//                     }
//                 }

//                 // Calculate average price and compared_at_price
//                 $averagePrice = round(array_sum($prices) / count($prices), 2);
//                 $comparedAtPrice = round($averagePrice * fake()->randomFloat(2, 1.05, 1.25), 2);

//                 $product->update([
//                     'average_price' => $averagePrice,
//                     'compared_at_price' => $comparedAtPrice,
//                 ]);
//             });
//         });
//     }
// }















namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantOption;
use App\Models\ProductVariantValue;
use App\Models\VariantOption;
use App\Models\VariantOptionValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $allOptions = VariantOption::with('values')->get();

            // Create 50 products
            Product::factory()->count(50)->create()->each(function ($product) use ($allOptions) {
                $selectedOptions = $allOptions->random(rand(1, 3)); // Pick 1-3 variant options
                $productOptionValues = [];

                // Attach selected variant options and pick 2–5 values for each
                foreach ($selectedOptions as $option) {
                    ProductVariantOption::firstOrCreate([
                        'product_id' => $product->id,
                        'variant_option_id' => $option->id,
                    ]);

                    $values = $option->values->shuffle()->take(rand(2, 5));
                    $productOptionValues[$option->id] = $values;
                }

                // Create all combinations of selected values
                $combinations = $this->generateCombinations(array_values($productOptionValues));

                $prices = [];

                foreach ($combinations as $combo) {
                    $price = fake()->randomFloat(2, 20, 1000);
                    $comboKey = collect($combo)->pluck('value')->implode(' - ');

                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'price' => $price,
                        'stock' => fake()->numberBetween(1, 50),
                        'combo_key' => $comboKey,
                    ]);

                    foreach ($combo as $value) {
                        ProductVariantValue::create([
                            'product_variant_id' => $variant->id,
                            'variant_option_value_id' => $value->id,
                        ]);
                    }

                    $prices[] = $price;
                }

              
            });
        });
    }

    private function generateCombinations($arrays, $i = 0)
    {
        if (!isset($arrays[$i])) {
            return [[]];
        }

        $tmp = $this->generateCombinations($arrays, $i + 1);

        $result = [];

        foreach ($arrays[$i] as $value) {
            foreach ($tmp as $t) {
                $result[] = array_merge([$value], $t);
            }
        }

        return $result;
    }
}