<?php

namespace WeDevelop\StatamicGrid\Services;

class PendingGridData
{
    protected array $pending = [];

    protected array $pendingBySlug = [];

    /**
     * Store grid data for an entry ID.
     */
    public function store(string $entryId, array $data): void
    {
        $this->pending[$entryId] = $data;
    }

    /**
     * Store grid data by slug (for new entries without ID yet).
     */
    public function storeBySlug(string $slug, array $data): void
    {
        $this->pendingBySlug[$slug] = $data;
    }

    /**
     * Check if pending data exists for an entry ID.
     */
    public function has(string $entryId): bool
    {
        return isset($this->pending[$entryId]);
    }

    /**
     * Check if pending data exists by slug.
     */
    public function hasBySlug(string $slug): bool
    {
        return isset($this->pendingBySlug[$slug]);
    }

    /**
     * Get and remove pending data for an entry ID.
     */
    public function pull(string $entryId): array
    {
        $data = $this->pending[$entryId] ?? [];
        unset($this->pending[$entryId]);

        return $data;
    }

    /**
     * Get and remove pending data by slug.
     */
    public function pullBySlug(string $slug): array
    {
        $data = $this->pendingBySlug[$slug] ?? [];
        unset($this->pendingBySlug[$slug]);

        return $data;
    }
}