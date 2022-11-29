<?php

namespace Rikkei\CallApi;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\CallApi\Providers\RouteServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(RIKKEI_CALL_API_PATH . 'resources/views', 'call_api');
        $this->loadTranslationsFrom(RIKKEI_CALL_API_PATH . 'resources/lang', 'call_api');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if (!defined('RIKKEI_CALL_API_PATH')) {
            define('RIKKEI_CALL_API_PATH', __DIR__ . '/../');
        }

        $providers = [
            RouteServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}
