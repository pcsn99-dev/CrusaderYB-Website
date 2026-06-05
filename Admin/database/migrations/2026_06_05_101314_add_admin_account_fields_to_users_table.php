<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'must_change_password')) {
                    $table->boolean('must_change_password')
                        ->default(false)
                        ->after('active');
                }

                if (! Schema::hasColumn('users', 'temporary_password_expires_at')) {
                    $table->timestamp('temporary_password_expires_at')
                        ->nullable()
                        ->after('must_change_password');
                }

                if (! Schema::hasColumn('users', 'last_login_at')) {
                    $table->timestamp('last_login_at')
                        ->nullable()
                        ->after('temporary_password_expires_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'last_login_at')) {
                    $table->dropColumn('last_login_at');
                }

                if (Schema::hasColumn('users', 'temporary_password_expires_at')) {
                    $table->dropColumn('temporary_password_expires_at');
                }

                if (Schema::hasColumn('users', 'must_change_password')) {
                    $table->dropColumn('must_change_password');
                }
            });
        }
    }
};