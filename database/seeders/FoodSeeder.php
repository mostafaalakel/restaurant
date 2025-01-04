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
            'name' => [
                'en' => 'Cheese Pizza',
                'ar' => 'بيتزا بالجبن',
            ],
            'category_id' => 1,
            'price' => 9.99,
            'image' => 'cheese_pizza.jpg',
            'description' => [
                'en' => 'Delicious cheese pizza with fresh ingredients',
                'ar' => 'بيتزا لذيذة بالجبن مع مكونات طازجة',
            ],
            'stock' => 50,
        ]);

        Food::create([
            'name' =>[
                'en' => 'Grilled Chicken',
                'ar' => 'دجاج مشوي',
            ],
            'category_id' => 2,
            'price' => 15.50,
            'image' => 'grilled_chicken.jpg',
            'description' => [
                'en' => 'Juicy grilled chicken with spices',
                'ar' => 'دجاج مشوي طري مع التوابل',
            ],
            'stock' => 30,
        ]);
    }
}
