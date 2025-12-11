<?php

namespace WeDevelop\StatamicGrid\Elements;

use Illuminate\Database\Schema\Blueprint;
use Statamic\Facades\Asset;
use WeDevelop\StatamicGrid\Models\GridElement;

class ImageElement extends Element
{
    public static function handle(): string
    {
        return 'image';
    }

    public static function name(): string
    {
        return 'Image';
    }

    public static function icon(): string
    {
        return 'media-image-picture-orientation';
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
                'handle' => 'image',
                'field' => [
                    'type' => 'assets',
                    'display' => 'Image',
                    'container' => 'assets',
                    'max_files' => 1,
                ],
            ],
            [
                'handle' => 'alt',
                'field' => [
                    'type' => 'text',
                    'display' => 'Alt Text',
                ],
            ],
        ];
    }

    public static function migrationColumns(Blueprint $table): void
    {
        $table->string('title')->nullable();
        $table->string('image')->nullable();
        $table->string('alt')->nullable();
    }

    /**
     * Process data for storage.
     * Extract asset reference from array format.
     */
    public static function processForStorage(array $data): array
    {
        $processed = parent::processForStorage($data);

        // Assets fieldtype returns array, we store the reference string
        if (isset($processed['image']) && is_array($processed['image'])) {
            $processed['image'] = $processed['image'][0] ?? null;
        }

        return $processed;
    }

    /**
     * Load data for editing.
     * Wrap image reference in array for assets fieldtype.
     */
    public static function loadForEditing(GridElement $element): array
    {
        $data = parent::loadForEditing($element);

        // Assets fieldtype expects array format
        if (! empty($data['image']) && ! is_array($data['image'])) {
            $data['image'] = [$data['image']];
        }

        return $data;
    }

    /**
     * Augment data for frontend rendering.
     * Resolves asset to full data with URL, path, alt, etc.
     */
    public static function augment(GridElement $element): array
    {
        return [
            'title' => $element->title,
            'image' => static::resolveAsset($element->image),
            'alt' => $element->alt,
        ];
    }

    /**
     * Resolve an asset reference to augmented data.
     */
    protected static function resolveAsset(?string $assetReference): ?array
    {
        if (empty($assetReference)) {
            return null;
        }

        $asset = Asset::find($assetReference);

        if (! $asset) {
            return null;
        }

        return [
            'url' => $asset->url(),
            'path' => $asset->path(),
            'alt' => $asset->get('alt'),
            'id' => $asset->id(),
        ];
    }
}
