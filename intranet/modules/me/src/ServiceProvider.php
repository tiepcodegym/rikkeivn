<?php

namespace Rikkei\Me;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\Me\Providers\RouteServiceProvider;
use Rikkei\Me\Providers\DatabaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(RIKKEI_ME_PATH . 'resources/views', 'me');
        $this->loadTranslationsFrom(RIKKEI_ME_PATH . 'resources/lang', 'me');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_ME_PATH')) {
            define('RIKKEI_ME_PATH', __DIR__ . '/../');
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
