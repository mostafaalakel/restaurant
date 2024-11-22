<?php

namespace Database\Seeders;

use App\Models\Food;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Food::create([
            'name' => json_encode([
                'en' => 'Cheese Pizza',
                'ar' => 'بيتزا بالجبن',
            ]),
            'category_id' => 1,
            'price' => 9.99,
            'image' => 'cheese_pizza.jpg',
            'description' => json_encode([
                'en' => 'Delicious cheese pizza with fresh ingredients',
                'ar' => 'بيتزا لذيذة بالجبن مع مكونات طازجة',
            ]),
            'quantity' => 50,
        ],JSON_UNESCAPED_UNICODE);

        Food::create([
            'name' => json_encode([
                'en' => 'Grilled Chicken',
                'ar' => 'دجاج مشوي',
            ]),
            'category_id' => 2,
            'price' => 15.50,
            'image' => 'grilled_chicken.jpg',
            'description' => json_encode([
                'en' => 'Juicy grilled chicken with spices',
                'ar' => 'دجاج مشوي طري مع التوابل',
            ]),
            'quantity' => 30,
        ],JSON_UNESCAPED_UNICODE);
    }
}
