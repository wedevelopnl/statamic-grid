<?php

namespace WeDevelop\StatamicGrid\Commands;

use Illuminate\Console\Command;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Entry as EntryFacade;
use WeDevelop\StatamicGrid\Models\GridArea;
use WeDevelop\StatamicGrid\Services\GridDataService;

class MigrateGridData extends Command
{
    protected $signature = 'statamic-grid:migrate
                            {--dry-run : Preview changes without saving}
                            {--force : Force migration even if grid data already exists in database}';

    protected $description = 'Migrate grid data from JSON to normalized database tables';

    protected GridDataService $gridService;

    protected int $migratedCount = 0;

    protected int $skippedCount = 0;

    public function __construct(GridDataService $gridService)
    {
        parent::__construct();
        $this->gridService = $gridService;
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $this->info($dryRun ? 'Running in dry-run mode...' : 'Starting migration...');
        $this->newLine();

        EntryFacade::all()->each(fn (Entry $entry) => $this->processEntry($entry, $dryRun));

        $this->displaySummary($dryRun);

        return Command::SUCCESS;
    }

    protected function processEntry(Entry $entry, bool $dryRun): void
    {
        $gridData = $entry->get('grid');

        if (empty($gridData) || ! is_array($gridData)) {
            return;
        }

        if ($this->shouldSkipEntry($entry)) {
            return;
        }

        $this->info("Processing entry: {$entry->id()} - {$entry->title}");

        if ($dryRun) {
            $this->displayMigrationPreview($gridData);
        } else {
            $this->migrateEntry($entry, $gridData);
        }

        $this->migratedCount++;
    }

    protected function shouldSkipEntry(Entry $entry): bool
    {
        if ($this->option('force')) {
            return false;
        }

        if (GridArea::where('entry_id', $entry->id())->exists()) {
            $this->warn("Skipping entry {$entry->id()} ({$entry->title}) - already has grid data in database");
            $this->skippedCount++;

            return true;
        }

        return false;
    }

    protected function migrateEntry(Entry $entry, array $gridData): void
    {
        $this->gridService->saveGridToDatabase($entry->id(), $gridData);

        // Clear the grid field from the JSON data column
        $entry->set('grid', null);
        $entry->save();
    }

    protected function displayMigrationPreview(array $gridData): void
    {
        $stats = $this->calculateGridStats($gridData);

        $this->line("  - {$stats['sections']} sections, {$stats['rows']} rows, {$stats['elements']} elements");
    }

    /**
     * Calculate statistics for grid data.
     *
     * @return array{sections: int, rows: int, elements: int}
     */
    protected function calculateGridStats(array $gridData): array
    {
        $stats = ['sections' => 0, 'rows' => 0, 'elements' => 0];

        foreach ($gridData as $sectionData) {
            if (! isset($sectionData['rows'])) {
                continue;
            }

            $stats['sections']++;

            foreach ($sectionData['rows'] ?? [] as $rowData) {
                if (! isset($rowData['elements'])) {
                    continue;
                }

                $stats['rows']++;
                $stats['elements'] += count($rowData['elements']);
            }
        }

        return $stats;
    }

    protected function displaySummary(bool $dryRun): void
    {
        $this->newLine();

        $action = $dryRun ? 'would be migrated' : 'migrated';
        $this->info("Migration complete. {$this->migratedCount} entries {$action}, {$this->skippedCount} skipped.");
    }
}
