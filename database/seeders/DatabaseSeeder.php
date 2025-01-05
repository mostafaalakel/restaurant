<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            AdminSeeder::class,
            CategorySeeder::class,
            FoodSeeder::class,
            CartSeeder::class,
            GeneralDiscountSeeder::class,
            CodeDiscountSeeder::class,
            FoodGeneralDiscountSeeder::class,
            FoodCodeDiscountSeeder::class,
        ]);
    }
}
