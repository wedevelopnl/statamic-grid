<?php

namespace WeDevelop\StatamicGrid\Elements;

use Illuminate\Database\Schema\Blueprint;
use WeDevelop\StatamicGrid\Contracts\ElementContract;
use WeDevelop\StatamicGrid\Models\GridElement;

/**
 * Abstract base class for all grid elements.
 *
 * Each element type (Text, Image, Video, etc.) must extend this class
 * and implement all abstract methods.
 *
 * To create a new element type:
 * 1. Create a class extending Element
 * 2. Implement all abstract methods
 * 3. Create a migration for the element's table
 * 4. Register in your ServiceProvider: ElementRegistry::register(YourElement::class)
 */
abstract class Element implements ElementContract
{
    /**
     * Get the unique handle for this element type.
     */
    abstract public static function handle(): string;

    /**
     * Get the display name for this element type.
     */
    abstract public static function name(): string;

    /**
     * Get the icon name for this element type.
     */
    abstract public static function icon(): string;

    /**
     * Get the field definitions for this element type.
     */
    abstract public static function fields(): array;

    /**
     * Define the database columns for this element type.
     */
    abstract public static function migrationColumns(Blueprint $table): void;

    /**
     * Get the database table name for this element type.
     * Default: grid_element_{handle}s (e.g., grid_element_texts)
     */
    public static function tableName(): string
    {
        return 'grid_element_'.static::handle().'s';
    }

    /**
     * Process data for storage in the database.
     * Override in subclasses for custom processing.
     */
    public static function processForStorage(array $data): array
    {
        $fields = static::fields();
        $processed = [];

        foreach ($fields as $fieldDef) {
            $handle = $fieldDef['handle'];
            if (array_key_exists($handle, $data)) {
                $processed[$handle] = $data[$handle];
            }
        }

        return $processed;
    }

    /**
     * Load data for editing in the form.
     * Override in subclasses for custom loading.
     */
    public static function loadForEditing(GridElement $element): array
    {
        $fields = static::fields();
        $data = [];

        foreach ($fields as $fieldDef) {
            $handle = $fieldDef['handle'];
            $data[$handle] = $element->{$handle};
        }

        return $data;
    }

    /**
     * Augment data for frontend rendering.
     * Override in subclasses for custom augmentation (e.g., asset resolution).
     */
    public static function augment(GridElement $element): array
    {
        return static::loadForEditing($element);
    }

    /**
     * Get field handles for this element type.
     */
    public static function fieldHandles(): array
    {
        return array_map(fn ($field) => $field['handle'], static::fields());
    }

    /**
     * Get default values for fields.
     */
    public static function fieldDefaults(): array
    {
        $defaults = [];

        foreach (static::fields() as $field) {
            if (isset($field['field']['default'])) {
                $defaults[$field['handle']] = $field['field']['default'];
            }
        }

        return $defaults;
    }
}
