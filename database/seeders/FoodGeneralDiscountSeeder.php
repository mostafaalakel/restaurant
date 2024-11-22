<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FoodGeneralDiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('food_general_discount')->insert([
            'food_id' => 1,
            'general_discount_id' => 1,
        ]);

        DB::table('food_general_discount')->insert([
            'food_id' => 2,
            'general_discount_id' => 1,
        ]);
    }
}
