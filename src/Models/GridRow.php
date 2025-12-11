<?php

namespace WeDevelop\StatamicGrid\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use WeDevelop\StatamicGrid\Grid\Row;

class GridRow extends Model
{
    use HasUuids;

    protected $table = 'grid_rows';

    protected $guarded = [];

    public function section(): BelongsTo
    {
        return $this->belongsTo(GridSection::class, 'section_id');
    }

    public function elements(): HasMany
    {
        return $this->hasMany(GridElement::class, 'row_id')->orderBy('order');
    }

    /**
     * Get custom field values from this row.
     */
    public function getFieldValues(): array
    {
        $values = [];

        foreach (Row::fieldHandles() as $handle) {
            $values[$handle] = $this->{$handle};
        }

        return $values;
    }
}
