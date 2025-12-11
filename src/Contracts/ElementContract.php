<?php

namespace WeDevelop\StatamicGrid\Contracts;

use Illuminate\Database\Schema\Blueprint;
use WeDevelop\StatamicGrid\Models\GridElement;

interface ElementContract
{
    /**
     * Get the unique handle for this element type.
     * E.g., 'text', 'image', 'video'
     */
    public static function handle(): string;

    /**
     * Get the display name for this element type.
     * E.g., 'Text', 'Image', 'Video'
     */
    public static function name(): string;

    /**
     * Get the icon name for this element type.
     * Uses Statamic's icon set.
     */
    public static function icon(): string;

    /**
     * Get the field definitions for this element type.
     * Format: [['handle' => 'field_name', 'field' => ['type' => '...', 'display' => '...']]]
     */
    public static function fields(): array;

    /**
     * Get the database table name for this element type.
     */
    public static function tableName(): string;

    /**
     * Define the database columns for this element type.
     * Called during migration generation.
     */
    public static function migrationColumns(Blueprint $table): void;

    /**
     * Process data for storage in the database.
     * Transform incoming form data before saving.
     */
    public static function processForStorage(array $data): array;

    /**
     * Load data for editing in the form.
     * Transform database data for the edit form.
     */
    public static function loadForEditing(GridElement $element): array;

    /**
     * Augment data for frontend rendering.
     * Transform database data for template output.
     */
    public static function augment(GridElement $element): array;
}
