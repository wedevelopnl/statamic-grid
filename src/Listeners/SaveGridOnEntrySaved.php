<?php

namespace WeDevelop\StatamicGrid\Listeners;

use Illuminate\Support\Facades\Log;
use Statamic\Events\EntrySaved;
use WeDevelop\StatamicGrid\Services\GridDataService;
use WeDevelop\StatamicGrid\Services\PendingGridData;

class SaveGridOnEntrySaved
{
    public function __construct(
        protected GridDataService $gridService,
        protected PendingGridData $pendingData
    ) {}

    /**
     * Handle EntrySaved event - persist grid data to database.
     */
    public function handle(EntrySaved $event): void
    {
        $entry = $event->entry;
        $entryId = $entry->id();
        $slug = $entry->slug();

        Log::info('Grid: EntrySaved', ['entryId' => $entryId, 'slug' => $slug]);

        // Try by entry ID first, then fall back to slug (for new entries)
        if ($entryId && $this->pendingData->has($entryId)) {
            Log::info('Grid: Found pending data by entryId');
            $data = $this->pendingData->pull($entryId);
        } elseif ($slug && $this->pendingData->hasBySlug($slug)) {
            Log::info('Grid: Found pending data by slug');
            $data = $this->pendingData->pullBySlug($slug);
        } else {
            Log::info('Grid: No pending data found');
            return;
        }

        Log::info('Grid: Saving to database', ['entryId' => $entryId, 'dataCount' => count($data)]);
        $this->gridService->saveGridToDatabase($entryId, $data);
    }
}