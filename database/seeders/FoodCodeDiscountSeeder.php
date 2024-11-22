<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FoodCodeDiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('food_code_discount')->insert([
            'food_id' => 1,
            'code_discount_id' => 1,
        ]);

        DB::table('food_code_discount')->insert([
            'food_id' => 2,
            'code_discount_id' => 1,
        ]);
    }
}
