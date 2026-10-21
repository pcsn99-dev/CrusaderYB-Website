<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penalties', function (Blueprint $table) {
            $table
                ->tinyInteger('is_paid')
                ->nullable()
                ->change();

            $table
                ->string('status', 150)
                ->nullable()
                ->change();

            $table
                ->string('receipt_id', 255)
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('penalties', function (Blueprint $table) {
            $table
                ->tinyInteger('is_paid')
                ->nullable(false)
                ->change();

            $table
                ->string('status', 150)
                ->nullable(false)
                ->change();

            $table
                ->string('receipt_id', 255)
                ->nullable(false)
                ->change();
        });
    }
};