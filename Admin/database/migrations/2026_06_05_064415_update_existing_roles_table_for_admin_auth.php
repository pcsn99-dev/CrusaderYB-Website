<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                if (! Schema::hasColumn('roles', 'slug')) {
                    $table->string('slug')->nullable()->after('name');
                }

                if (! Schema::hasColumn('roles', 'description')) {
                    $table->text('description')->nullable()->after('guard_name');
                }

                if (! Schema::hasColumn('roles', 'is_protected')) {
                    $table->boolean('is_protected')->default(false)->after('description');
                }
            });

            DB::table('roles')
                ->whereNull('slug')
                ->orWhere('slug', '')
                ->orderBy('id')
                ->get()
                ->each(function ($role) {
                    DB::table('roles')
                        ->where('id', $role->id)
                        ->update([
                            'slug' => str($role->name)->slug()->toString(),
                        ]);
                });

            Schema::table('roles', function (Blueprint $table) {
                $table->string('slug')->nullable(false)->change();
                $table->unique('slug');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                if (Schema::hasColumn('roles', 'slug')) {
                    $table->dropUnique(['slug']);
                    $table->dropColumn('slug');
                }

                if (Schema::hasColumn('roles', 'description')) {
                    $table->dropColumn('description');
                }

                if (Schema::hasColumn('roles', 'is_protected')) {
                    $table->dropColumn('is_protected');
                }
            });
        }
    }
};