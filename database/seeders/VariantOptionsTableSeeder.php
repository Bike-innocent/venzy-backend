<?php



// namespace Database\Seeders;

// use Illuminate\Database\Seeder;
// use App\Models\VariantOption;
// use App\Models\VariantOptionValue;

// class VariantOptionsTableSeeder extends Seeder
// {
//     public function run(): void
//     {
//         $data = [
//             'Colour' => ['Red', 'Blue', 'Green', 'Black', 'White', 'Yellow', 'Purple'],
//             'Size' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
//             'Material' => ['Cotton', 'Polyester', 'Wool', 'Linen', 'Silk'],
//             'Style' => ['Casual', 'Formal', 'Sport', 'Vintage'],
//             'Pattern' => ['Solid', 'Striped', 'Checked', 'Printed'],
//         ];

//         foreach ($data as $optionName => $values) {
//             $option = VariantOption::firstOrCreate(['name' => $optionName]);

//             foreach ($values as $value) {
//                 VariantOptionValue::firstOrCreate([
//                     'variant_option_id' => $option->id,
//                     'value' => $value,
//                 ], [
//                     'hex_code' => $optionName === 'Colour' ? fake()->hexColor() : null,
//                 ]);
//             }
//         }
//     }
// }





namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VariantOption;
use App\Models\VariantOptionValue;

class VariantOptionsTableSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Colour' => ['Red', 'Blue', 'Green', 'Black', 'White', 'Yellow', 'Purple', 'Orange', 'Pink', 'Brown', 'Gray', 'Silver', 'Gold'],
            'Size' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'],
            'Material' => ['Cotton', 'Polyester', 'Wool', 'Linen', 'Silk', 'Leather', 'Denim', 'Chiffon'],
            'Style' => ['Casual', 'Formal', 'Sport', 'Vintage', 'Bohemian', 'Streetwear', 'Elegant'],
            'Pattern' => ['Solid', 'Striped', 'Checked', 'Printed', 'Paisley', 'Floral', 'Camouflage'],
        ];

        foreach ($data as $optionName => $values) {
            $option = VariantOption::firstOrCreate(['name' => $optionName]);

            foreach ($values as $value) {
                VariantOptionValue::firstOrCreate(
                    [
                        'variant_option_id' => $option->id,
                        'value' => $value,
                    ],
                    [
                        'hex_code' => $optionName === 'Colour' ? $this->getHexColor($value) : null,
                    ]
                );
            }
        }
    }

    /**
     * Optional: Map color names to hex codes, or generate dynamically.
     */
    protected function getHexColor($colorName)
    {
        $colors = [
            'Red' => '#FF0000',
            'Blue' => '#0000FF',
            'Green' => '#008000',
            'Black' => '#000000',
            'White' => '#FFFFFF',
            'Yellow' => '#FFFF00',
            'Purple' => '#800080',
            'Orange' => '#FFA500',
            'Pink' => '#FFC0CB',
            'Brown' => '#A52A2A',
            'Gray' => '#808080',
            'Silver' => '#C0C0C0',
            'Gold' => '#FFD700',
        ];

        return $colors[$colorName] ?? null;
    }
}