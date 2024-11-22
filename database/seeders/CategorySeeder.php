<?php

namespace Database\Seeders;

use App\Models\category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create([
            'name' => json_encode([
                'en' => 'Appetizers',
                'ar' => 'المقبلات',
            ],JSON_UNESCAPED_UNICODE),
        ]);

        Category::create([
            'name' => json_encode([
                'en' => 'Main Dishes',
                'ar' => 'الأطباق الرئيسية',
            ],JSON_UNESCAPED_UNICODE),
        ]);
    }
}
