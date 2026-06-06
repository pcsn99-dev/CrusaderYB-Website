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
                if (! Schema::hasColumn('users', 'google_id')) {
                    $table->string('google_id')
                        ->nullable()
                        ->after('remember_token')
                        ->index();
                }

                if (! Schema::hasColumn('users', 'avatar')) {
                    $table->string('avatar')
                        ->nullable()
                        ->after('google_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'avatar')) {
                    $table->dropColumn('avatar');
                }

                if (Schema::hasColumn('users', 'google_id')) {
                    $table->dropColumn('google_id');
                }
            });
        }
    }
};