<?php

namespace Database\Seeders;

use App\Models\GeneralDiscount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GeneralDiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        GeneralDiscount::create([
            'name' => json_encode([
                'en' => 'Holiday Sale',
                'ar' => 'تخفيضات العطلات',
            ],JSON_UNESCAPED_UNICODE),
            'value' => 10.00,
            'start_date' => now(),
            'end_date' => now()->addDays(10),
        ],JSON_UNESCAPED_UNICODE);
    }
}
