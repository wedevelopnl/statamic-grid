<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fix foreign key mismatch error in SQLite.
 *
 * The original migration defined entry_id as UUID with a foreign key to entries.id,
 * but Statamic's entries table uses 'text' type for id, causing SQLite errors.
 * This migration recreates the table without the foreign key constraint.
 */
return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support ALTER TABLE DROP CONSTRAINT, so we need to recreate the table
        if (DB::getDriverName() === 'sqlite') {
            // Temporarily disable foreign key checks
            DB::statement('PRAGMA foreign_keys = OFF');

            // Create new table without foreign key
            Schema::create('grid_areas_new', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('entry_id')->unique();
                $table->timestamps();
                $table->index('entry_id');
            });

            // Copy existing data
            DB::statement('INSERT INTO grid_areas_new (id, entry_id, created_at, updated_at) SELECT id, entry_id, created_at, updated_at FROM grid_areas');

            // Drop old table and rename new one
            Schema::drop('grid_areas');
            Schema::rename('grid_areas_new', 'grid_areas');

            // Re-enable foreign key checks
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            // For other databases, we can use standard ALTER TABLE
            Schema::table('grid_areas', function (Blueprint $table) {
                // Try to drop foreign key if it exists
                try {
                    $table->dropForeign(['entry_id']);
                } catch (\Exception $e) {
                    // Foreign key may not exist, ignore
                }

                // Change column type from uuid to string
                $table->string('entry_id')->change();
            });
        }
    }

    public function down(): void
    {
        // We don't restore the foreign key on rollback since it causes issues
    }
};
