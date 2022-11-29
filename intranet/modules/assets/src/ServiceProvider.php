<?php

namespace Rikkei\Assets;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\Assets\Providers\RouteServiceProvider;
use Rikkei\Assets\Providers\DatabaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(RIKKEI_ASSETS_PATH . 'resources/views', 'asset');
        $this->loadTranslationsFrom(RIKKEI_ASSETS_PATH . 'resources/lang', 'asset');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if (!defined('RIKKEI_ASSETS_PATH')) {
            define('RIKKEI_ASSETS_PATH', __DIR__ . '/../');
        }

        $providers = [
            RouteServiceProvider::class,
            DatabaseServiceProvider::class
        ];

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}
