<?php

namespace WeDevelop\StatamicGrid\Tags;

use Statamic\Tags\Tags;

class Grid extends Tags
{
    public function render()
    {
        $grid = $this->params->get('data') ?? $this->context->get('grid');

        if (!$grid) {
            return '';
        }

        return view('statamic-grid::grid', ['grid' => $grid])->render();
    }
}
