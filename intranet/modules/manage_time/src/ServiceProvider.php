<?php

namespace Rikkei\ManageTime;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\ManageTime\Providers\RouteServiceProvider;
use Rikkei\ManageTime\Providers\DatabaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(RIKKEI_MANAGETIME_PATH . 'resources/views', 'manage_time');
        $this->loadTranslationsFrom(RIKKEI_MANAGETIME_PATH . 'resources/lang', 'manage_time');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_MANAGETIME_PATH')) {
            define('RIKKEI_MANAGETIME_PATH', __DIR__ . '/../');
        }
        
        $providers = [
            RouteServiceProvider::class,
            DatabaseServiceProvider::class,
            Providers\ComposerServiceProvider::class
        ];

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}
