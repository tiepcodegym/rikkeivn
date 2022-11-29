<?php

namespace Rikkei\Notes;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\Notes\Providers\RouteServiceProvider;
use Rikkei\Notes\Providers\DatabaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(RIKKEI_NOTES_PATH . 'resources/views', 'notes');
        $this->loadTranslationsFrom(RIKKEI_NOTES_PATH . 'resources/lang', 'notes');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_NOTES_PATH')) {
            define('RIKKEI_NOTES_PATH', __DIR__ . '/../');
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
