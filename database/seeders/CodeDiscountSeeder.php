<?php

namespace Database\Seeders;

use App\Models\CodeDiscount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CodeDiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        CodeDiscount::create([
            'name' => 'Welcome Discount',
            'code' => 'WELCOME10',
            'value' => 15.00,
            'start_date' => now(),
            'end_date' => now()->addDays(7),
        ]);
    }
}
