<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Colour;

class ColoursTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed 10 random colours
        Colour::factory()->count(50)->create();
    }
}