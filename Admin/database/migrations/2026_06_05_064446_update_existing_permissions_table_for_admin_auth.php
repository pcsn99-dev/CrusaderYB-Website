<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('permissions')) {
            Schema::table('permissions', function (Blueprint $table) {
                if (! Schema::hasColumn('permissions', 'slug')) {
                    $table->string('slug')->nullable()->after('name');
                }

                if (! Schema::hasColumn('permissions', 'description')) {
                    $table->text('description')->nullable()->after('guard_name');
                }
            });

            DB::table('permissions')
                ->whereNull('slug')
                ->orWhere('slug', '')
                ->orderBy('id')
                ->get()
                ->each(function ($permission) {
                    DB::table('permissions')
                        ->where('id', $permission->id)
                        ->update([
                            'slug' => str($permission->name)->slug()->toString(),
                        ]);
                });

            Schema::table('permissions', function (Blueprint $table) {
                $table->string('slug')->nullable(false)->change();
                $table->unique('slug');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('permissions')) {
            Schema::table('permissions', function (Blueprint $table) {
                if (Schema::hasColumn('permissions', 'slug')) {
                    $table->dropUnique(['slug']);
                    $table->dropColumn('slug');
                }

                if (Schema::hasColumn('permissions', 'description')) {
                    $table->dropColumn('description');
                }
            });
        }
    }
};