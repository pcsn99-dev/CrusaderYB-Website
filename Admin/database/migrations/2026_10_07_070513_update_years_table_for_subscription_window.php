<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('years', function (Blueprint $table) {
            $table->date('subscription_start')
                ->nullable()
                ->after('status');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('years', function (Blueprint $table) {
            $table->dropColumn('subscription_start');
            $table->dropTimestamps();
        });
    }
};