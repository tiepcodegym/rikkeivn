<?php

namespace Rikkei\SubscriberNotify;

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
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'SubscriberNotify');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'SubscriberNotify');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if (!defined('RIKKEI_SUBSCRIBER_NOTIFY_PATH')) {
            define('RIKKEI_SUBSCRIBER_NOTIFY_PATH', __DIR__ . '/../');
        }

        $providers = [
            \Rikkei\SubscriberNotify\Providers\RouteServiceProvider::class
        ];

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}