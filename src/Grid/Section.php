<?php

namespace WeDevelop\StatamicGrid\Grid;

use Illuminate\Database\Schema\Blueprint;

/**
 * Section class - defines section-level fields and database structure.
 *
 * Sections are the top-level organizational units within a grid.
 * They contain rows, which contain elements.
 *
 * To extend sections at project level, create a custom class
 * and register it via config (future feature).
 */
class Section
{
    /**
     * Get the field definitions for sections.
     * Override in subclasses to add/modify fields.
     */
    public static function fields(): array
    {
        return [
            [
                'handle' => 'background_color',
                'field' => [
                    'type' => 'select',
                    'display' => 'Background Color',
                    'options' => [
                        'light' => 'Light',
                        'dark' => 'Dark',
                    ],
                    'default' => 'light',
                ],
            ],
        ];
    }

    /**
     * Get the database table name for sections.
     */
    public static function tableName(): string
    {
        return 'grid_sections';
    }

    /**
     * Define the database columns for sections.
     */
    public static function migrationColumns(Blueprint $table): void
    {
        $table->string('background_color')->default('light');
    }

    /**
     * Get field handles for sections.
     */
    public static function fieldHandles(): array
    {
        return array_map(fn ($field) => $field['handle'], static::fields());
    }

    /**
     * Get default values for section fields.
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
     * Extract section field values from input data.
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
     * Extract section field values with defaults applied.
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
        return 'rows';
    }
}
