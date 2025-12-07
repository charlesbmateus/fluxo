<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Enums\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('Test1234567'),
            'role' => Role::ADMIN,
        ]);

        // Provider User
        User::create([
            'name' => 'John The Cleaner',
            'email' => 'provider@example.com',
            'password' => Hash::make('Test1234567'),
            'role' => Role::PROVIDER,
        ]);

        // Client User
        User::create([
            'name' => 'Jane Client',
            'email' => 'client@example.com',
            'password' => Hash::make('Test1234567'),
            'role' => Role::CLIENT,
        ]);
    }
}
