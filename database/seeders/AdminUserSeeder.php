<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the initial administrator account securely from environment variables.
     *
     * Credentials are read from ADMIN_EMAIL and ADMIN_PASSWORD (.env),
     * hashed securely, and never hardcoded into source code.
     */
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');
        $adminName = env('ADMIN_NAME', 'Administrator');

        if (empty($adminEmail) || empty($adminPassword)) {
            $this->command?->warn('AdminUserSeeder skipped: ADMIN_EMAIL or ADMIN_PASSWORD is not set in your .env file.');
            return;
        }

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => Hash::make($adminPassword),
                'is_admin' => true,
            ]
        );

        $this->command?->info("Admin user successfully seeded for [{$adminEmail}].");
    }
}

