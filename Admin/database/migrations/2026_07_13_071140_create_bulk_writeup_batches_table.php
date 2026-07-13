<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_writeup_batches', function (Blueprint $table) {
            $table->id();

            $table->string('year', 20);

            $table->unsignedInteger('college_id')->nullable();

            $table->foreign('college_id')
                ->references('id')
                ->on('colleges')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->unsignedInteger('created_count')->default(0);

            /*
             * Undo information.
             */
            $table->foreignId('undone_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->unsignedInteger('undone_count')->nullable();
            $table->timestamp('undone_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_writeup_batches');
    }
};