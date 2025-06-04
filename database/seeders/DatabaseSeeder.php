<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([

            RolesAndPermissionsSeeder::class,
            AdminSeeder::class,


            UsersTableSeeder::class,
            AddressesTableSeeder::class,

            CategorySeeder::class,
            BrandsTableSeeder::class,

           VariantOptionsTableSeeder::class,
            ProductsTableSeeder::class,

            OrderSeeder::class,
            OrderItemSeeder::class,
            ProductImageSeeder::class,













            // PaymentSeeder::class,








        ]);
    }
}
