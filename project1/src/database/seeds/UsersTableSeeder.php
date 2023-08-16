<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\User;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Youssef Admin',
            'email' => 'youssef.y.najdi@gmail.com',
            'password' => Hash::make('secret'), // Hash the password
            'isAdmin' => 1
        ]);

        User::create([
            'name' => 'Youssef Guest',
            'email' => 'youssef.y.najdi@example.com',
            'password' => Hash::make('secret'), // Hash the password
            'isAdmin' => 0
        ]);
    }
}