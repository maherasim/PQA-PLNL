<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'], // Search criteria
            [
                'name' => 'Admin',
                'email' => 'admin@example.com', // Ensure email is set
                'password' => Hash::make('password'),
            ]
        );
    }
}
