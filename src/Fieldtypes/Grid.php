<?php

namespace WeDevelop\StatamicGrid\Fieldtypes;

use Statamic\Contracts\Entries\Entry;
use Statamic\Fieldtypes\Replicator;
use WeDevelop\StatamicGrid\Grid\Row;
use WeDevelop\StatamicGrid\Grid\Section;
use WeDevelop\StatamicGrid\Registries\ElementRegistry;
use WeDevelop\StatamicGrid\Services\GridDataService;

class Grid extends Replicator
{
    protected static $handle = 'database-grid';

    protected GridDataService $gridService;

    public function __construct()
    {
        $this->gridService = app(GridDataService::class);
    }

    /**
     * Use the existing Replicator Vue component.
     */
    public function component(): string
    {
        return 'replicator';
    }

    /**
     * Process data from frontend to database storage.
     */
    public function process(mixed $data): mixed
    {
        $entry = $this->getEntry();

        if (! $entry) {
            return parent::process($data);
        }

        $this->gridService->saveGridToDatabase($entry->id(), $data ?? []);

        // Store entry ID as marker so preProcess can load from database
        return ['__entry_id' => $entry->id()];
    }

    /**
     * PreProcess data from database to frontend format.
     */
    public function preProcess(mixed $data): array
    {
        $entryId = $this->resolveEntryId($data);

        if (! $entryId) {
            return parent::preProcess($data ?? []);
        }

        $gridData = $this->gridService->loadGridForEditing($entryId);

        return parent::preProcess($gridData);
    }

    /**
     * Augment data for frontend display.
     */
    public function augment(mixed $value): array
    {
        $entry = $this->getEntry();

        if (! $entry) {
            return parent::augment($value ?? []);
        }

        $gridData = $this->gridService->loadGridForFrontend($entry->id());

        return parent::augment($gridData);
    }

    /**
     * Resolve the entry ID from data or field parent.
     */
    protected function resolveEntryId(mixed $data): ?string
    {
        // Try stored marker first (for post-save response)
        if (is_array($data) && isset($data['__entry_id'])) {
            return $data['__entry_id'];
        }

        // Fall back to field parent
        return $this->getEntry()?->id();
    }

    /**
     * Get the entry from the field's parent.
     */
    protected function getEntry(): ?Entry
    {
        $parent = $this->field?->parent();

        return $parent instanceof Entry ? $parent : null;
    }

    /**
     * Override config to always use hardcoded sets structure.
     */
    public function config(?string $key = null, $fallback = null)
    {
        // If requesting a specific key, handle sets specially
        if ($key === 'sets') {
            return $this->gridSetsForVue();
        }

        // For other keys, use parent behavior
        if ($key !== null) {
            $value = parent::config($key, $fallback);

            // Apply defaults for our config options
            if ($key === 'collapse' && $value === null) {
                return true;
            }
            if ($key === 'previews' && $value === null) {
                return false;
            }

            return $value;
        }

        // When requesting all config, merge our sets
        $config = parent::config() ?? [];

        if (! is_array($config)) {
            $config = [];
        }

        $config['sets'] = $this->gridSetsForVue();
        $config['collapse'] = $config['collapse'] ?? true;
        $config['previews'] = $config['previews'] ?? false;

        return $config;
    }

    /**
     * Override to always return our hardcoded sets structure.
     */
    public function flattenedSetsConfig()
    {
        return collect($this->gridSetsRaw())->flatMap(function ($group) {
            return $group['sets'];
        });
    }

    /**
     * Configuration fields shown in the blueprint editor.
     */
    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Appearance & Behavior'),
                'fields' => [
                    'collapse' => [
                        'display' => __('Collapse'),
                        'type' => 'select',
                        'cast_booleans' => true,
                        'options' => [
                            'false' => __('Disabled'),
                            'true' => __('Enabled'),
                            'accordion' => __('Accordion'),
                        ],
                        'default' => true,
                    ],
                    'previews' => [
                        'display' => __('Field Previews'),
                        'type' => 'toggle',
                        'default' => false,
                    ],
                    'max_sets' => [
                        'display' => __('Max Sets'),
                        'type' => 'integer',
                    ],
                    'fullscreen' => [
                        'display' => __('Allow Fullscreen Mode'),
                        'type' => 'toggle',
                        'default' => true,
                    ],
                    // Hidden sets field - always uses our hardcoded structure
                    'sets' => [
                        'type' => 'hidden',
                        'default' => $this->gridSetsForVue(),
                    ],
                ],
            ],
        ];
    }

    /**
     * Raw sets structure used internally by Replicator.
     * Uses Section, Row, and ElementRegistry as sources of truth.
     */
    protected function gridSetsRaw(): array
    {
        return [
            'main' => [
                'display' => 'Sections',
                'sets' => [
                    'section' => [
                        'display' => 'Section',
                        'icon' => 'array',
                        'fields' => $this->sectionFields(),
                    ],
                ],
            ],
        ];
    }

    /**
     * Sets structure formatted for the Vue component.
     */
    protected function gridSetsForVue(): array
    {
        return $this->transformSetsForVue($this->gridSetsRaw());
    }

    /**
     * Transform sets from raw format to Vue-compatible format.
     */
    protected function transformSetsForVue(array $sets): array
    {
        return collect($sets)->map(function ($group, $groupHandle) {
            return [
                'handle' => $groupHandle,
                'display' => $group['display'] ?? null,
                'sets' => collect($group['sets'])->map(function ($set, $setHandle) {
                    return [
                        'handle' => $setHandle,
                        'id' => $setHandle,
                        'display' => $set['display'] ?? null,
                        'icon' => $set['icon'] ?? null,
                        'fields' => $this->transformFieldsForVue($set['fields'] ?? []),
                    ];
                })->values()->all(),
            ];
        })->values()->all();
    }

    /**
     * Transform fields from raw format to Vue-compatible format.
     */
    protected function transformFieldsForVue(array $fields): array
    {
        return collect($fields)->map(function ($field) {
            $config = $field['field'];
            $config['handle'] = $field['handle'];

            // Recursively process nested replicator sets
            if (isset($config['sets'])) {
                $config['sets'] = $this->transformSetsForVue($config['sets']);
            }

            return $config;
        })->all();
    }

    /**
     * Fields for a section set.
     * Uses Section class as single source of truth.
     */
    protected function sectionFields(): array
    {
        return array_merge(Section::fields(), [
            [
                'handle' => Section::nestedContentField(),
                'field' => [
                    'display' => 'Rows',
                    'type' => 'replicator',
                    'collapse' => true,
                    'previews' => false,
                    'button_label' => 'Add row',
                    'sets' => [
                        'main' => [
                            'display' => 'Rows',
                            'sets' => [
                                'row' => [
                                    'display' => 'Row',
                                    'icon' => 'array',
                                    'fields' => $this->rowFields(),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Fields for a row set.
     * Uses Row class and ElementRegistry as sources of truth.
     */
    protected function rowFields(): array
    {
        return array_merge(Row::fields(), [
            [
                'handle' => Row::nestedContentField(),
                'field' => [
                    'display' => 'Elements',
                    'type' => 'replicator',
                    'collapse' => true,
                    'previews' => false,
                    'button_label' => 'Add element',
                    'sets' => [
                        'main' => [
                            'display' => 'Elements',
                            'sets' => ElementRegistry::getElementSets(),
                        ],
                    ],
                ],
            ],
        ]);
    }
}
