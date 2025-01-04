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
            'name' => [
                'en' => 'Holiday Sale',
                'ar' => 'تخفيضات العطلات',
            ],
            'value' => 10.00,
            'start_date' => now(),
            'end_date' => now()->addDays(10),
        ]);
    }
}
