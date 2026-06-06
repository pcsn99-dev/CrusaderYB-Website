<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')
            ->whereNull('username')
            ->orWhere('username', '')
            ->update([
                'type' => 'admin',
            ]);

        DB::table('users')
            ->where('email', 'superadmin@cyb.local')
            ->update([
                'type' => 'admin',
            ]);

        DB::table('users')
            ->whereNotNull('username')
            ->where('username', '!=', '')
            ->where('email', '!=', 'superadmin@cyb.local')
            ->whereNull('role_id')
            ->update([
                'type' => 'student',
            ]);
    }
}