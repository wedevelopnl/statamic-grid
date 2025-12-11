<?php

namespace WeDevelop\StatamicGrid\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use WeDevelop\StatamicGrid\Grid\Section;

class GridSection extends Model
{
    use HasUuids;

    protected $table = 'grid_sections';

    protected $guarded = [];

    public function gridArea(): BelongsTo
    {
        return $this->belongsTo(GridArea::class);
    }

    public function rows(): HasMany
    {
        return $this->hasMany(GridRow::class, 'section_id')->orderBy('order');
    }

    /**
     * Get custom field values from this section.
     */
    public function getFieldValues(): array
    {
        $values = [];

        foreach (Section::fieldHandles() as $handle) {
            $values[$handle] = $this->{$handle};
        }

        return $values;
    }
}
