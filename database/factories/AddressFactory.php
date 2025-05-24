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
use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Check if the user already has any addresses
        $user = User::factory()->create();


        return [
            'user_id' => $user->id,
            'name' => $this->faker->name(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
            'country' => $this->faker->country(),
        ];
    }
}
