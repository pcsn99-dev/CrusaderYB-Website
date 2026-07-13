<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('writeups', function (Blueprint $table) {
            $table->foreignId('generic_writeup_id')
                ->nullable()
                ->after('student_info_id')
                ->constrained('generic_writeups')
                ->nullOnDelete();

            $table->foreignId('bulk_writeup_batch_id')
                ->nullable()
                ->after('generic_writeup_id')
                ->constrained('bulk_writeup_batches')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('writeups', function (Blueprint $table) {
            $table->dropForeign(['bulk_writeup_batch_id']);
            $table->dropForeign(['generic_writeup_id']);

            $table->dropColumn([
                'bulk_writeup_batch_id',
                'generic_writeup_id',
            ]);
        });
    }
};