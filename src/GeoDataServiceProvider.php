<?php

namespace RahasIstiyak\GeoData;

use Illuminate\Support\ServiceProvider;

class GeoDataServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/geo-data.php' => config_path('geo-data.php'),
        ], 'geo-data-config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/geo-data.php', 'geo-data');
    }
}