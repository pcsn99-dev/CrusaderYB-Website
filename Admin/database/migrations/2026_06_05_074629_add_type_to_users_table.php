<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'type')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('type')
                    ->default('student')
                    ->after('role_id');
            });

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
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'type')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};