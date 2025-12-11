<?php

namespace WeDevelop\StatamicGrid\Tags;

use Illuminate\Support\Collection;
use Statamic\Tags\Tags;
use WeDevelop\StatamicGrid\Services\GridDataService;

class Grid extends Tags
{
    protected GridDataService $gridService;

    public function __construct()
    {
        $this->gridService = app(GridDataService::class);
    }

    /**
     * Render the grid for the current entry.
     */
    public function render(): string
    {
        // First try to get data from params or context (for backward compatibility)
        $grid = $this->params->get('data') ?? $this->context->get('grid');

        if ($this->isValidGridData($grid)) {
            return $this->renderGrid($grid);
        }

        // Load from database using entry ID
        $entryId = $this->getEntryId();

        if (! $entryId) {
            return '';
        }

        $grid = $this->gridService->loadGridForFrontend($entryId);

        if (empty($grid)) {
            return '';
        }

        return $this->renderGrid($grid);
    }

    /**
     * Check if grid data is valid and non-empty.
     */
    protected function isValidGridData(mixed $grid): bool
    {
        if ($grid instanceof Collection) {
            return $grid->isNotEmpty();
        }

        return is_array($grid) && count($grid) > 0;
    }

    /**
     * Render the grid view.
     */
    protected function renderGrid(array|Collection $grid): string
    {
        return view('statamic-grid::grid', ['grid' => $grid])->render();
    }

    /**
     * Get the current entry ID from the context.
     */
    protected function getEntryId(): ?string
    {
        // Try explicit parameter first
        if ($entryId = $this->params->get('entry')) {
            return (string) $entryId;
        }

        // Try common context keys
        foreach (['id', 'entry_id'] as $key) {
            if ($entryId = $this->context->get($key)) {
                return (string) $entryId;
            }
        }

        // Try to get from page or entry objects
        foreach (['page', 'entry'] as $key) {
            $object = $this->context->get($key);
            if ($object && method_exists($object, 'id')) {
                return $object->id();
            }
        }

        return null;
    }
}
