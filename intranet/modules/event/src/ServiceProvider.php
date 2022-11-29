<?php

namespace Rikkei\Event;

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
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'event');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'event');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_EVENT_PATH')) {
            define('RIKKEI_EVENT_PATH', __DIR__ . '/../');
        }
        
        $providers = [
            \Rikkei\Event\Providers\RouteServiceProvider::class,
            \Rikkei\Event\Providers\DatabaseServiceProvider::class
        ];

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}
