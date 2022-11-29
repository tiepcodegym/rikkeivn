<?php

namespace Rikkei\Magazine;

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
        $this->loadViewsFrom(RIKKEI_MAGAZINE_PATH . 'resources/views', 'magazine');
        $this->loadTranslationsFrom(RIKKEI_MAGAZINE_PATH . 'resources/lang', 'magazine');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_MAGAZINE_PATH')) {
            define('RIKKEI_MAGAZINE_PATH', __DIR__ . '/../');
        }
        $providers = [
            \Rikkei\Magazine\Providers\DatabaseServiceProvider::class,
            \Rikkei\Magazine\Providers\RouteServiceProvider::class,
            \Rikkei\Magazine\Providers\ValidatorServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}
