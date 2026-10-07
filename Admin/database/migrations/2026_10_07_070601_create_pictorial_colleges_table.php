<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pictorial_colleges', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('pictorial_id');
            $table->unsignedInteger('college_id');

            $table->timestamps();

            $table->unique([
                'pictorial_id',
                'college_id',
            ]);

            $table->foreign('pictorial_id')
                ->references('id')
                ->on('pictorials')
                ->cascadeOnDelete();

            $table->foreign('college_id')
                ->references('id')
                ->on('colleges')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pictorial_colleges');
    }
};