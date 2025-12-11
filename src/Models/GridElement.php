<?php

namespace WeDevelop\StatamicGrid\Models;

use Facades\Statamic\Fieldtypes\RowId;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use WeDevelop\StatamicGrid\Registries\ElementRegistry;

class GridElement extends Model
{
    use HasUuids;

    protected $table = 'grid_elements';

    protected $guarded = [];

    protected $casts = [
        'content' => 'array',
    ];

    public function row(): BelongsTo
    {
        return $this->belongsTo(GridRow::class, 'row_id');
    }

    /**
     * Get the element type class.
     *
     * @return class-string<\WeDevelop\StatamicGrid\Elements\Element>|null
     */
    public function getElementTypeClass(): ?string
    {
        return ElementRegistry::get($this->type);
    }

    /**
     * Get element data formatted for the edit form.
     */
    public function toEditableArray(): array
    {
        $data = [
            RowId::handle() => $this->id,
            'type' => $this->type,
            'enabled' => true,
        ];

        $elementClass = $this->getElementTypeClass();

        if ($elementClass) {
            $data = array_merge($data, $elementClass::loadForEditing($this));
        }

        return $data;
    }

    /**
     * Augment this element for frontend rendering.
     */
    public function toAugmentedArray(): array
    {
        $data = ['type' => $this->type];

        $elementClass = $this->getElementTypeClass();

        if ($elementClass) {
            $data = array_merge($data, $elementClass::augment($this));
        }

        return $data;
    }
}
