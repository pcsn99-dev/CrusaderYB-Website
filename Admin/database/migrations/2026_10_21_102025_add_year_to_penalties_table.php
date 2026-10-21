<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penalties', function (Blueprint $table) {
            $table
                ->string('year', 20)
                ->nullable()
                ->after('amount')
                ->index();
        });

        /*
        |--------------------------------------------------------------------------
        | Backfill Existing Penalties
        |--------------------------------------------------------------------------
        |
        | All existing penalties belong to CYB 2026.
        |
        */

        DB::table('penalties')
            ->whereNull('year')
            ->update([
                'year' => '2026',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Require Year Going Forward
        |--------------------------------------------------------------------------
        */

        Schema::table('penalties', function (Blueprint $table) {
            $table
                ->string('year', 20)
                ->nullable(false)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('penalties', function (Blueprint $table) {
            $table->dropIndex([
                'year',
            ]);

            $table->dropColumn(
                'year'
            );
        });
    }
};