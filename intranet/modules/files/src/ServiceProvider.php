<?php

namespace Rikkei\Files;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\Files\Providers\RouteServiceProvider;
use Rikkei\Files\Providers\DatabaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'files');
        $this->loadTranslationsFrom( __DIR__.'/../resources/lang', 'files');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $providers = [
            RouteServiceProvider::class,
            DatabaseServiceProvider::class
        ];

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}
