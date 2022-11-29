<?php

namespace Rikkei\Emailnoti;

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
        $this->loadViewsFrom( RIKKEI_EMAILNOTI_PATH . 'resources/views', 'emailnoti');
        $this->loadTranslationsFrom( RIKKEI_EMAILNOTI_PATH . 'resources/lang', 'emailnoti');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_EMAILNOTI_PATH')) {
            define('RIKKEI_EMAILNOTI_PATH', __DIR__ . '/../');
        }
        $providers = [
            \Rikkei\Emailnoti\Providers\RouteServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}
