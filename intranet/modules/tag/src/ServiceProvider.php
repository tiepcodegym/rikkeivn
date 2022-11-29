<?php

namespace Rikkei\Tag;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\Tag\Providers\RouteServiceProvider;
use Rikkei\Tag\Providers\DatabaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom( RIKKEI_TAG_PATH . 'resources/views', 'tag');
        $this->loadTranslationsFrom( RIKKEI_TAG_PATH . 'resources/lang', 'tag');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_TAG_PATH')) {
            define('RIKKEI_TAG_PATH', __DIR__ . '/../');
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
