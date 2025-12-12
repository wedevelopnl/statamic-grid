<?php

namespace WeDevelop\StatamicGrid;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        Tags\Grid::class,
    ];

    public function bootAddon()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'statamic-grid');

        $this->publishes([
            __DIR__.'/../resources/fieldsets' => resource_path('fieldsets/vendor/statamic-grid'),
        ], 'statamic-grid');
    }
}
