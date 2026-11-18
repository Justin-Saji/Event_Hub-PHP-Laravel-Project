<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed events
        $this->call(EventSeeder::class);

        // 2. Seed initial admin account from environment variables
        $this->call(AdminUserSeeder::class);

        // 3. Ensure a standard test user exists for non-admin permission testing
        $normalEmail = env('TEST_USER_EMAIL', 'user@example.com');
        $normalPassword = env('TEST_USER_PASSWORD', 'TestUserSecret2026!');

        User::updateOrCreate(
            ['email' => $normalEmail],
            [
                'name' => 'Standard User',
                'password' => Hash::make($normalPassword),
                'is_admin' => false,
            ]
        );
    }
}
