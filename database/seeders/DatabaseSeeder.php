<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed Prime Property agent accounts (no self-registration).
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'superadmin@primeproperty.id'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('SuperAdmin@123'),
                'role' => User::ROLE_SUPERADMIN,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@primeproperty.id'],
            [
                'name' => 'Admin Agent',
                'password' => Hash::make('AdminAgent@123'),
                'role' => User::ROLE_ADMIN,
            ],
        );

        $this->call(PropertySeeder::class);
    }
}
