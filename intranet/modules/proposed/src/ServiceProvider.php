<?php

namespace Rikkei\Proposed;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\Proposed\Providers\RouteServiceProvider;
use Rikkei\Proposed\Providers\DatabaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(RIKKEI_MOBILE_PROPOSED_PATH . 'resources/views', 'proposed');
        $this->loadTranslationsFrom(RIKKEI_MOBILE_PROPOSED_PATH . 'resources/lang', 'proposed');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if (!defined('RIKKEI_MOBILE_PROPOSED_PATH')) {
            define('RIKKEI_MOBILE_PROPOSED_PATH', __DIR__ . '/../');
        }
        $providers = [
            RouteServiceProvider::class,
            DatabaseServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}
