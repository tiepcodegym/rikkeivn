<?php

namespace Rikkei\Help;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\Help\Providers\RouteServiceProvider;
use Rikkei\Help\Providers\DatabaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(RIKKEI_HELP_PATH . 'resources/views', 'help');
        $this->loadTranslationsFrom(RIKKEI_HELP_PATH . 'resources/lang', 'help');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_HELP_PATH')) {
            define('RIKKEI_HELP_PATH', __DIR__ . '/../');
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
