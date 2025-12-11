<?php

namespace WeDevelop\StatamicGrid\Elements;

use Illuminate\Database\Schema\Blueprint;
use Statamic\Fieldtypes\Bard;
use WeDevelop\StatamicGrid\Models\GridElement;

class TextElement extends Element
{
    public static function handle(): string
    {
        return 'text';
    }

    public static function name(): string
    {
        return 'Text';
    }

    public static function icon(): string
    {
        return 'text-formatting-all-caps';
    }

    public static function fields(): array
    {
        return [
            [
                'handle' => 'title',
                'field' => [
                    'type' => 'text',
                    'display' => 'Title',
                ],
            ],
            [
                'handle' => 'content',
                'field' => [
                    'type' => 'bard',
                    'display' => 'Content',
                    'remove_empty_nodes' => false,
                ],
            ],
        ];
    }

    public static function migrationColumns(Blueprint $table): void
    {
        $table->string('title')->nullable();
        $table->jsonb('content')->nullable();
    }

    /**
     * Augment data for frontend rendering.
     * Converts Bard JSON to HTML.
     */
    public static function augment(GridElement $element): array
    {
        return [
            'title' => $element->title,
            'content' => static::augmentBardContent($element->content),
        ];
    }

    /**
     * Convert Bard ProseMirror JSON to HTML.
     */
    protected static function augmentBardContent(?array $content): string
    {
        if (empty($content)) {
            return '';
        }

        return (new Bard)->augment($content);
    }
}
