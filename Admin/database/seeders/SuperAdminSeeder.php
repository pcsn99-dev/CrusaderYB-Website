<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('slug', 'super-admin')->first();

        if (! $superAdminRole) {
            $this->command->error('Super Admin role not found. Run RolesAndPermissionsSeeder first.');
            return;
        }

        User::updateOrCreate(
            ['email' => 'superadmin@cyb.local'],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => Hash::make('password'),
                'role_id' => $superAdminRole->id,
                'type' => 'admin',
                'active' => 1,
                'email_verified_at' => now(),
            ]
        );
    }
}