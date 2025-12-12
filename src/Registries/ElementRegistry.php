<?php

namespace WeDevelop\StatamicGrid\Registries;

use InvalidArgumentException;
use WeDevelop\StatamicGrid\Elements\Element;

/**
 * Registry for element types.
 *
 * Element types must be registered before the grid fieldtype is used.
 * Register in your ServiceProvider's boot() method:
 *
 *     ElementRegistry::register(TextElement::class);
 *     ElementRegistry::register(ImageElement::class);
 *
 * For project-level custom elements:
 *
 *     // In App\Providers\AppServiceProvider::boot()
 *     ElementRegistry::register(App\Elements\VideoElement::class);
 */
class ElementRegistry
{
    /**
     * Registered element types.
     *
     * @var array<string, class-string<Element>>
     */
    protected static array $elements = [];

    /**
     * Register an element type.
     *
     * @param  class-string<Element>  $elementClass
     *
     * @throws InvalidArgumentException
     */
    public static function register(string $elementClass): void
    {
        if (! is_subclass_of($elementClass, Element::class)) {
            throw new InvalidArgumentException(
                "Element class [{$elementClass}] must extend ".Element::class
            );
        }

        static::$elements[$elementClass::handle()] = $elementClass;
    }

    /**
     * Get all registered element types.
     *
     * @return array<string, class-string<Element>>
     */
    public static function all(): array
    {
        return static::$elements;
    }

    /**
     * Get an element type class by handle.
     *
     * @return class-string<Element>|null
     */
    public static function get(string $handle): ?string
    {
        return static::$elements[$handle] ?? null;
    }

    /**
     * Check if an element type is registered.
     */
    public static function has(string $handle): bool
    {
        return isset(static::$elements[$handle]);
    }

    /**
     * Get element type handles.
     *
     * @return array<int, string>
     */
    public static function handles(): array
    {
        return array_keys(static::$elements);
    }

    /**
     * Get sets configuration for all registered elements.
     * Used by the Grid fieldtype to build the replicator config.
     *
     * @return array<string, array>
     */
    public static function getElementSets(): array
    {
        $sets = [];

        foreach (static::$elements as $handle => $class) {
            $sets[$handle] = [
                'display' => $class::name(),
                'icon' => $class::icon(),
                'fields' => $class::fields(),
            ];
        }

        return $sets;
    }

    /**
     * Get eager load relationships for all registered elements.
     * Used for optimized database queries.
     *
     * @return array<int, string>
     */
    public static function getEagerLoadRelationships(): array
    {
        return array_map(
            fn ($handle) => $handle,
            static::handles()
        );
    }

    /**
     * Get full nested eager load paths for grid queries.
     *
     * @return array<int, string>
     */
    public static function getGridEagerLoadRelationships(): array
    {
        $relationships = [];

        foreach (static::handles() as $handle) {
            $relationships[] = "sections.rows.elements.{$handle}";
        }

        return $relationships;
    }

    /**
     * Clear all registered elements.
     * Useful for testing.
     */
    public static function clear(): void
    {
        static::$elements = [];
    }
}
