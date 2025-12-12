<?php

namespace WeDevelop\StatamicGrid\Grid;

use Illuminate\Database\Schema\Blueprint;

/**
 * Row class - defines row-level fields and database structure.
 *
 * Rows are containers within sections that hold elements.
 * By default, rows have no custom fields - they just organize elements.
 *
 * To extend rows at project level, create a custom class
 * and register it via config (future feature).
 */
class Row
{
    /**
     * Get the field definitions for rows.
     * Override in subclasses to add fields.
     */
    public static function fields(): array
    {
        return [];
    }

    /**
     * Get the database table name for rows.
     */
    public static function tableName(): string
    {
        return 'grid_rows';
    }

    /**
     * Define the database columns for rows.
     */
    public static function migrationColumns(Blueprint $table): void
    {
        // No additional columns by default
    }

    /**
     * Get field handles for rows.
     */
    public static function fieldHandles(): array
    {
        return array_map(fn ($field) => $field['handle'], static::fields());
    }

    /**
     * Get default values for row fields.
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

    /**
     * Extract row field values from input data.
     */
    public static function extractFieldValues(array $data): array
    {
        $values = [];

        foreach (static::fieldHandles() as $handle) {
            if (array_key_exists($handle, $data)) {
                $values[$handle] = $data[$handle];
            }
        }

        return $values;
    }

    /**
     * Extract row field values with defaults applied.
     */
    public static function extractFieldValuesWithDefaults(array $data): array
    {
        return array_merge(static::fieldDefaults(), static::extractFieldValues($data));
    }

    /**
     * Get the nested content field name.
     */
    public static function nestedContentField(): string
    {
        return 'elements';
    }
}
