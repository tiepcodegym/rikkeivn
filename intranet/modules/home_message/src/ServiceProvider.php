<?php

namespace Rikkei\HomeMessage;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\HomeMessage\Providers\DatabaseServiceProvider;
use Rikkei\HomeMessage\Providers\RouteServiceProvider;

class ServiceProvider extends BaseServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'HomeMessage');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'HomeMessage');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if (!defined('RIKKEI_HOME_MESSAGE_PATH')) {
            define('RIKKEI_HOME_MESSAGE_PATH', __DIR__ . '/../');
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