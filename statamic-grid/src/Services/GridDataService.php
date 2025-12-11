<?php

namespace WeDevelop\StatamicGrid\Services;

use Facades\Statamic\Fieldtypes\RowId;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use WeDevelop\StatamicGrid\Grid\Row;
use WeDevelop\StatamicGrid\Grid\Section;
use WeDevelop\StatamicGrid\Models\GridArea;
use WeDevelop\StatamicGrid\Models\GridElement;
use WeDevelop\StatamicGrid\Models\GridRow;
use WeDevelop\StatamicGrid\Models\GridSection;
use WeDevelop\StatamicGrid\Registries\ElementRegistry;

class GridDataService
{
    /**
     * Save grid structure to normalized database tables.
     */
    public function saveGridToDatabase(string $entryId, array $data): GridArea
    {
        return DB::transaction(function () use ($entryId, $data): GridArea {
            $gridArea = GridArea::firstOrCreate(['entry_id' => $entryId]);

            // Clear existing sections (cascade will handle rows and elements)
            $gridArea->sections()->delete();

            foreach ($data as $sectionIndex => $sectionData) {
                if (! $this->isValidSection($sectionData)) {
                    continue;
                }

                $section = $this->createSection($gridArea, $sectionData, $sectionIndex);
                $this->processRows($section, $sectionData[Section::nestedContentField()] ?? []);
            }

            return $gridArea;
        });
    }

    /**
     * Load grid structure from database as array for editing.
     */
    public function loadGridForEditing(string $entryId): array
    {
        $gridArea = $this->loadGridArea($entryId);

        if (! $gridArea) {
            return [];
        }

        return $gridArea->sections
            ->sortBy('order')
            ->map(fn (GridSection $section) => $this->transformSectionForEditing($section))
            ->values()
            ->all();
    }

    /**
     * Load grid structure from database as array for frontend rendering.
     */
    public function loadGridForFrontend(string $entryId): array
    {
        $gridArea = $this->loadGridArea($entryId);

        if (! $gridArea) {
            return [];
        }

        return $gridArea->sections
            ->sortBy('order')
            ->map(fn (GridSection $section) => $this->transformSectionForFrontend($section))
            ->values()
            ->all();
    }

    /**
     * Load a GridArea with all relationships eager loaded.
     */
    protected function loadGridArea(string $entryId): ?GridArea
    {
        return GridArea::where('entry_id', $entryId)
            ->with(['sections.rows.elements'])
            ->first();
    }

    /**
     * Check if section data is valid.
     */
    protected function isValidSection(mixed $sectionData): bool
    {
        return is_array($sectionData) && isset($sectionData[Section::nestedContentField()]);
    }

    /**
     * Check if row data is valid.
     */
    protected function isValidRow(mixed $rowData): bool
    {
        return is_array($rowData) && isset($rowData[Row::nestedContentField()]);
    }

    /**
     * Create a section from data.
     */
    protected function createSection(GridArea $gridArea, array $sectionData, int $order): GridSection
    {
        $customFields = Section::extractFieldValuesWithDefaults($sectionData);

        return $gridArea->sections()->create(array_merge([
            'id' => $sectionData['_id'] ?? Str::uuid()->toString(),
            'order' => $order,
        ], $customFields));
    }

    /**
     * Process rows for a section.
     */
    protected function processRows(GridSection $section, array $rows): void
    {
        foreach ($rows as $rowIndex => $rowData) {
            if (! $this->isValidRow($rowData)) {
                continue;
            }

            $row = $this->createRow($section, $rowData, $rowIndex);
            $this->processElements($row, $rowData[Row::nestedContentField()] ?? []);
        }
    }

    /**
     * Create a row from data.
     */
    protected function createRow(GridSection $section, array $rowData, int $order): GridRow
    {
        $customFields = Row::extractFieldValuesWithDefaults($rowData);

        return $section->rows()->create(array_merge([
            'id' => $rowData['_id'] ?? Str::uuid()->toString(),
            'order' => $order,
        ], $customFields));
    }

    /**
     * Process elements for a row.
     */
    protected function processElements(GridRow $row, array $elements): void
    {
        foreach ($elements as $elementIndex => $elementData) {
            $this->createElement($row, $elementData, $elementIndex);
        }
    }

    /**
     * Create an element with its data.
     */
    protected function createElement(GridRow $row, array $elementData, int $order): GridElement
    {
        $type = $elementData['type'] ?? 'text';
        $elementClass = ElementRegistry::get($type);

        // Get processed field data from the element class
        $processedData = $elementClass
            ? $elementClass::processForStorage($elementData)
            : [];

        return $row->elements()->create(array_merge([
            'id' => $elementData['_id'] ?? Str::uuid()->toString(),
            'type' => $type,
            'order' => $order,
        ], $processedData));
    }

    /**
     * Transform a section for editing.
     */
    protected function transformSectionForEditing(GridSection $section): array
    {
        return array_merge([
            RowId::handle() => $section->id,
            'type' => 'section',
            'enabled' => true,
            Section::nestedContentField() => $section->rows
                ->sortBy('order')
                ->map(fn (GridRow $row) => $this->transformRowForEditing($row))
                ->values()
                ->all(),
        ], $section->getFieldValues());
    }

    /**
     * Transform a row for editing.
     */
    protected function transformRowForEditing(GridRow $row): array
    {
        return array_merge([
            RowId::handle() => $row->id,
            'type' => 'row',
            'enabled' => true,
            Row::nestedContentField() => $row->elements
                ->sortBy('order')
                ->map(fn (GridElement $element) => $element->toEditableArray())
                ->values()
                ->all(),
        ], $row->getFieldValues());
    }

    /**
     * Transform a section for frontend rendering.
     */
    protected function transformSectionForFrontend(GridSection $section): array
    {
        return array_merge([
            'type' => 'section',
            Section::nestedContentField() => $section->rows
                ->sortBy('order')
                ->map(fn (GridRow $row) => $this->transformRowForFrontend($row))
                ->values()
                ->all(),
        ], $section->getFieldValues());
    }

    /**
     * Transform a row for frontend rendering.
     */
    protected function transformRowForFrontend(GridRow $row): array
    {
        return array_merge([
            'type' => 'row',
            Row::nestedContentField() => $row->elements
                ->sortBy('order')
                ->map(fn (GridElement $element) => $element->toAugmentedArray())
                ->values()
                ->all(),
        ], $row->getFieldValues());
    }
}
