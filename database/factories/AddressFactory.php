<?php

// namespace Database\Factories;
// use App\Models\User;
// use Illuminate\Database\Eloquent\Factories\Factory;

// /**
//  * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
//  */
// class AddressFactory extends Factory
// {
//     /**
//      * Define the model's default state.
//      *
//      * @return array<string, mixed>
//      */
//     public function definition(): array
//     {
//         return [
//             'user_id' => User::factory(),
//             'address_line_1' => $this->faker->streetAddress(),
//             'address_line_2' => $this->faker->secondaryAddress(),
//             'city' => $this->faker->city(),
//             'state' => $this->faker->state(),
//             'postal_code' => $this->faker->postcode(),
//             'country' => $this->faker->country(),
//         ];
//     }
// }

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // Laravel will auto-create a user

            'full_name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'dial_code' => "+1",

            'address_line_1' => $this->faker->streetAddress(),
            'address_line_2' => $this->faker->optional()->secondaryAddress(),

            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            // 'postal_code' => $this->faker->postcode(),
            'country' => $this->faker->country(),

            'is_default' => false, 
        ];
    }
}