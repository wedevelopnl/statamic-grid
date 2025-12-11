<?php

namespace WeDevelop\StatamicGrid\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GridArea extends Model
{
    use HasUuids;

    protected $table = 'grid_areas';

    protected $fillable = [
        'id',
        'entry_id',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(
            config('statamic.eloquent-driver.entries.model'),
            'entry_id'
        );
    }

    public function sections(): HasMany
    {
        return $this->hasMany(GridSection::class)->orderBy('order');
    }
}
