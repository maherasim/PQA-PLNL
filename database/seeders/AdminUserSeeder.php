<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Status;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure a default status exists
        $status = Status::first();
        if (!$status) {
            $status = Status::create([
                'id' => (string) Str::uuid(),
                'status_name' => 'Active',
            ]);
        }

        User::withTrashed()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'full_name' => 'Admin',
                'email' => 'admin@example.com',
                'password_hash' => Hash::make('password'),
                'cvb_id' => 'CVB' . strtoupper(Str::random(10)),
                'status' => $status->id,
                'password_created_at' => now(),
                'password_last_changed' => now(),
            ]
        );
    }
}
