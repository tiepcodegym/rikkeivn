<?php

namespace Rikkei\Resource;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'resource');
        $this->loadTranslationsFrom( __DIR__ . '/../resources/lang', 'resource');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_RESOURCE_PATH')) {
            define('RIKKEI_RESOURCE_PATH', __DIR__ . '/../');
        }
        $providers = [
            \Rikkei\Resource\Providers\RouteServiceProvider::class,
            \Rikkei\Resource\Providers\DatabaseServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}
