<?php

namespace Rikkei\Document;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\Document\Providers\RouteServiceProvider;
use Rikkei\Document\Providers\DatabaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(RIKKEI_DOC_PATH . 'resources/views', 'doc');
        $this->loadTranslationsFrom(RIKKEI_DOC_PATH . 'resources/lang', 'doc');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_DOC_PATH')) {
            define('RIKKEI_DOC_PATH', __DIR__ . '/../');
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
