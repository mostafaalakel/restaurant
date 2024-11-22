<?php

namespace Database\Seeders;

use App\Models\Cart;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Cart::create([
            'user_id' => 1,
        ]);

        Cart::create([
            'user_id' => 2,
        ]);
    }
}
