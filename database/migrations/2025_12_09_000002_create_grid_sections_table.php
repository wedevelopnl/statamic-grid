<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grid_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('grid_area_id');
            $table->string('background_color')->default('light');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->foreign('grid_area_id')
                ->references('id')
                ->on('grid_areas')
                ->onDelete('cascade');

            $table->index(['grid_area_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grid_sections');
    }
};
