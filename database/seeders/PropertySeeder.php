<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PropertySeeder extends Seeder
{
    /**
     * Seed 60 sample properties for pagination testing on the frontend.
     */
    public function run(): void
    {
        $superadmin = User::query()->updateOrCreate(
            ['email' => 'superadmin@primeproperty.id'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('SuperAdmin@123'),
                'role' => User::ROLE_SUPERADMIN,
            ],
        );

        Property::factory()
            ->count(60)
            ->create([
                'created_by' => (string) $superadmin->id,
            ]);
    }
}
