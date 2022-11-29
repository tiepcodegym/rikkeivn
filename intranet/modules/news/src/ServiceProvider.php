<?php

namespace Rikkei\News;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\News\Providers\RouteServiceProvider;
use Rikkei\News\Providers\DatabaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(RIKKEI_NEWS_PATH . 'resources/views', 'news');
        $this->loadTranslationsFrom(RIKKEI_NEWS_PATH . 'resources/lang', 'news');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_NEWS_PATH')) {
            define('RIKKEI_NEWS_PATH', __DIR__ . '/../');
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
