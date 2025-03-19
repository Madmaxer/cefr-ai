<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'id' => '1d6fea35-453d-4d4f-b7a0-5362526ab238',
            'name' => 'User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}
