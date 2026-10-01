<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Create the admin portal login from ADMIN_EMAIL / ADMIN_PASSWORD in .env.
     * Existing accounts are left untouched so a password changed from the
     * portal is never reset by re-seeding.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'desh@dpspayments.com');
        $password = env('ADMIN_PASSWORD');

        if (blank($password)) {
            $this->command?->error('Set ADMIN_PASSWORD in .env before seeding the admin user.');
            return;
        }

        User::firstOrCreate(
            ['email' => $email],
            ['name' => 'DPS Admin', 'password' => $password]
        );
    }
}
