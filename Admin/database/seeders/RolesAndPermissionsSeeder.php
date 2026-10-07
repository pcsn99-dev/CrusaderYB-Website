<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'View Admin Dashboard',
                'slug' => 'view-admin-dashboard',
                'guard_name' => 'web',
                'description' => 'Allows access to the admin dashboard.',
            ],
            [
                'name' => 'Manage Roles',
                'slug' => 'manage-roles',
                'guard_name' => 'web',
                'description' => 'Allows creating, editing, deleting, and assigning role permissions.',
            ],
            [
                'name' => 'Manage Admin Users',
                'slug' => 'manage-admin-users',
                'guard_name' => 'web',
                'description' => 'Allows creating, editing, activating, and deactivating admin users.',
            ],
            [
                'name' => 'View Writeups',
                'slug' => 'view-writeups',
                'guard_name' => 'web',
                'description' => 'Allows viewing submitted student writeups.',
            ],
            [
                'name' => 'Proofread Writeups',
                'slug' => 'proofread-writeups',
                'guard_name' => 'web',
                'description' => 'Allows proofreading submitted student writeups.',
            ],
            [
                'name' => 'View Student Accounts',
                'slug' => 'view-student-accounts',
                'guard_name' => 'web',
                'description' => 'Allows viewing student account information.',
            ],
            [
                'name' => 'Manage Student Subscription',
                'slug' => 'manage-student-subscription',
                'guard_name' => 'web',
                'description' => 'Allows changing student subscription status.',
            ],
            [
                'name' => 'Manage Third Party Status',
                'slug' => 'manage-third-party-status',
                'guard_name' => 'web',
                'description' => 'Allows changing whether a student uses a third-party photo.',
            ],
            [
                'name' => 'Manage Years',
                'slug' => 'manage-years',
                'guard_name' => 'web',
                'description' => 'Allows creating, updating, and activating CYB years and subscription periods.',
            ],
            [
                'name' => 'View Pictorial Schedules',
                'slug' => 'view-pictorial-schedules',
                'guard_name' => 'web',
                'description' => 'Allows viewing pictorial schedules and their reservations.',
            ],
            [
                'name' => 'Manage Pictorial Schedules',
                'slug' => 'manage-pictorial-schedules',
                'guard_name' => 'web',
                'description' => 'Allows creating, bulk creating, deleting, and managing pictorial schedules.',
            ],


        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        $superAdmin = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'guard_name' => 'web',
                'description' => 'Has full access to all admin features.',
                'is_protected' => true,
            ]
        );

        $permissionIds = Permission::pluck('id')->toArray();

        $superAdmin->permissions()->sync($permissionIds);
    }
}