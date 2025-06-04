<?php



namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VariantOption;
use App\Models\VariantOptionValue;

class VariantOptionsTableSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Color' => ['Red', 'Blue', 'Green', 'Black', 'White', 'Yellow', 'Purple'],
            'Size' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
            'Material' => ['Cotton', 'Polyester', 'Wool', 'Linen', 'Silk'],
            'Style' => ['Casual', 'Formal', 'Sport', 'Vintage'],
            'Pattern' => ['Solid', 'Striped', 'Checked', 'Printed'],
        ];

        foreach ($data as $optionName => $values) {
            $option = VariantOption::firstOrCreate(['name' => $optionName]);

            foreach ($values as $value) {
                VariantOptionValue::firstOrCreate([
                    'variant_option_id' => $option->id,
                    'value' => $value,
                ], [
                    'hex_code' => $optionName === 'Color' ? fake()->hexColor() : null,
                ]);
            }
        }
    }
}
