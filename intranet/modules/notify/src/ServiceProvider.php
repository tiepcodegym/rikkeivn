<?php

namespace Rikkei\Notify;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\Notify\Providers\RouteServiceProvider;
use Rikkei\Notify\Providers\DatabaseServiceProvider;
use Rikkei\Notify\Providers\EventServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(RIKKEI_NOTIFY_PATH . 'resources/views', 'notify');
        $this->loadTranslationsFrom(RIKKEI_NOTIFY_PATH . 'resources/lang', 'notify');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if (!defined('RIKKEI_NOTIFY_PATH')) {
            define('RIKKEI_NOTIFY_PATH', __DIR__ . '/../');
        }

        $providers = [
            RouteServiceProvider::class,
            DatabaseServiceProvider::class,
            EventServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }

        $this->app->bind('RkNotify', 'Rikkei\Notify\Classes\RkNotify');
    }
}
