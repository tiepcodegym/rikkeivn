<?php

namespace Rikkei\Api;

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
        $this->loadTranslationsFrom(RIKKEI_API_PATH . 'resources/lang', 'api');
        $this->loadViewsFrom(RIKKEI_API_PATH . 'resources/views', 'api');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_API_PATH')) {
            define('RIKKEI_API_PATH', __DIR__ . '/../');
        }
        $providers = [
            \Rikkei\Api\Providers\DatabaseServiceProvider::class,
            \Rikkei\Api\Providers\RouteServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}
