<?php

namespace Rikkei\Statistic;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\Statistic\Providers\RouteServiceProvider;
use Rikkei\Statistic\Providers\DatabaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(RIKKEI_STATISTIC_PATH . 'resources/views', 'statistic');
        $this->loadTranslationsFrom(RIKKEI_STATISTIC_PATH . 'resources/lang', 'statistic');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if (!defined('RIKKEI_STATISTIC_PATH')) {
            define('RIKKEI_STATISTIC_PATH', __DIR__ . '/../');
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
