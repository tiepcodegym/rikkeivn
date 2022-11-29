<?php

namespace Rikkei\Vote;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\Vote\Providers\RouteServiceProvider;
use Rikkei\Vote\Providers\DatabaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(RIKKEI_VOTE_PATH . 'resources/views', 'vote');
        $this->loadTranslationsFrom(RIKKEI_VOTE_PATH . 'resources/lang', 'vote');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_VOTE_PATH')) {
            define('RIKKEI_VOTE_PATH', __DIR__ . '/../');
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
