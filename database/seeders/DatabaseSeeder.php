<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default admin (same credentials as the original app) - change the password after first login.
        User::firstOrCreate(
            ['username' => 'admin'],
            ['password' => 'admin123', 'role' => 'Admin'],
        );
    }
}
