<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grid_elements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('row_id');
            $table->string('type');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            // TextElement fields
            $table->string('title')->nullable();
            $table->jsonb('content')->nullable();

            // ImageElement fields
            $table->string('image')->nullable();
            $table->string('alt')->nullable();

            $table->foreign('row_id')
                ->references('id')
                ->on('grid_rows')
                ->onDelete('cascade');

            $table->index(['row_id', 'order']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grid_elements');
    }
};
