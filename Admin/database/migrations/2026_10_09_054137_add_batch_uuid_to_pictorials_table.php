<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pictorials', function (Blueprint $table) {
            $table->uuid('batch_uuid')
                ->nullable()
                ->after('is_delayed')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('pictorials', function (Blueprint $table) {
            $table->dropIndex(['batch_uuid']);
            $table->dropColumn('batch_uuid');
        });
    }
};