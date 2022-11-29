<?php

namespace Rikkei\Ot;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\Ot\Providers\RouteServiceProvider;
use Rikkei\Ot\Providers\DatabaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(RIKKEI_OT_PATH . 'resources/views', 'ot');
        $this->loadTranslationsFrom(RIKKEI_OT_PATH . 'resources/lang', 'ot');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_OT_PATH')) {
            define('RIKKEI_OT_PATH', __DIR__ . '/../');
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
