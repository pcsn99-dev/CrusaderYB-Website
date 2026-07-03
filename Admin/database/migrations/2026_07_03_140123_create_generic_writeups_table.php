<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generic_writeups', function (Blueprint $table) {
            $table->id();

            $table->string('year');

            $table->unsignedInteger('college_id');
            $table->foreign('college_id')
                ->references('id')
                ->on('colleges')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('title')->nullable();
            $table->longText('content');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['year', 'college_id']);
            $table->index(['year', 'college_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generic_writeups');
    }
};