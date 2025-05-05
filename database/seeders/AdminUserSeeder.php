<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'f_name' => 'Admin',
            'l_name' => 'User',
            'email' => 'admin@mail.com',
            'password' => Hash::make('codeastro.com'),
            'role' => 'admin',
            'image' => 'default.jpg'
        ]);
    }
}