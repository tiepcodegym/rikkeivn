<?php

namespace Rikkei\Contact;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\Contact\Providers\RouteServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom( RIKKEI_CONTACT_PATH . 'resources/views', 'contact');
        $this->loadTranslationsFrom( RIKKEI_CONTACT_PATH . 'resources/lang', 'contact');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_CONTACT_PATH')) {
            define('RIKKEI_CONTACT_PATH', __DIR__ . '/../');
        }
        $providers = [
            RouteServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}
