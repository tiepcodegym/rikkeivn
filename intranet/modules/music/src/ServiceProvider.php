<?php

namespace Rikkei\Music;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\Music\Providers\RouteServiceProvider;
use Rikkei\Music\Providers\DatabaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(RIKKEI_MUSIC_PATH . 'resources/views', 'music');
        $this->loadTranslationsFrom(RIKKEI_MUSIC_PATH . 'resources/lang', 'music');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_MUSIC_PATH')) {
            define('RIKKEI_MUSIC_PATH', __DIR__ . '/../');
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
