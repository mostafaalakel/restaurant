<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        User::create([
            'name' => 'ali',
            'email' => 'ali@example.com',
            'password' => Hash::make(0000),
            'provider' => 'locale',
        ]);

        User::create([
            'name' => 'Google User',
            'email' => 'google@example.com',
            'google_id' => 'google12345',
            'provider' => 'google',
        ]);
    }
}
