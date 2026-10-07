<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pictorials', function (Blueprint $table) {
            $table->unsignedInteger('college_id')
                ->nullable()
                ->change();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('pictorials', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropTimestamps();

            $table->unsignedInteger('college_id')
                ->nullable(false)
                ->change();
        });
    }
};