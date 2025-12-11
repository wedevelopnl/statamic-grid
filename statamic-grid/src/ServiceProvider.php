<?php

namespace WeDevelop\StatamicGrid;

use Statamic\Providers\AddonServiceProvider;
use WeDevelop\StatamicGrid\Commands\MigrateGridData;
use WeDevelop\StatamicGrid\Elements\ImageElement;
use WeDevelop\StatamicGrid\Elements\TextElement;
use WeDevelop\StatamicGrid\Fieldtypes\Grid;
use WeDevelop\StatamicGrid\Registries\ElementRegistry;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        Tags\Grid::class,
    ];

    protected $fieldtypes = [
        Grid::class,
    ];

    protected $commands = [
        MigrateGridData::class,
    ];

    public function bootAddon()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'statamic-grid');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->registerElements();
    }

    /**
     * Register all element types.
     *
     * Projects can register additional elements in their AppServiceProvider:
     *
     *     use WeDevelop\StatamicGrid\Registries\ElementRegistry;
     *     use App\Elements\VideoElement;
     *
     *     ElementRegistry::register(VideoElement::class);
     */
    protected function registerElements(): void
    {
        ElementRegistry::register(TextElement::class);
        ElementRegistry::register(ImageElement::class);
    }
}
