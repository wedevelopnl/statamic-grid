<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grid_rows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('section_id');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->foreign('section_id')
                ->references('id')
                ->on('grid_sections')
                ->onDelete('cascade');

            $table->index(['section_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grid_rows');
    }
};
