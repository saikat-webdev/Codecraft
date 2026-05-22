<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@codecraft.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create a test regular user
        User::firstOrCreate(
            ['email' => 'user@codecraft.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('user123'),
                'is_admin' => false,
                'email_verified_at' => now(),
            ]
        );
    }
}