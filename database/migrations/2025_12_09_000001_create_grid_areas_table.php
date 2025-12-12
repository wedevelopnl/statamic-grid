<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grid_areas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Use string instead of uuid to match Statamic's entries.id column type
            $table->string('entry_id')->unique();
            $table->timestamps();

            // Foreign key removed: Statamic's entries table uses 'text' type for id
            // which causes SQLite foreign key mismatch errors.
            // Data integrity is maintained through application logic.
            $table->index('entry_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grid_areas');
    }
};
