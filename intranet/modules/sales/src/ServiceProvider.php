<?php

namespace Rikkei\Sales;

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
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'sales');
        $this->loadTranslationsFrom( __DIR__ . '/../resources/lang', 'sales');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $providers = [
            \Rikkei\Sales\Providers\DatabaseServiceProvider::class,
            \Rikkei\Sales\Providers\RouteServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}
