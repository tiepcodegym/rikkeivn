<?php

namespace Rikkei\QA;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\QA\Providers\RouteServiceProvider;
use Rikkei\QA\Providers\DatabaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom( RIKKEI_QA_PATH . 'resources/views', 'qa');
        $this->loadTranslationsFrom( RIKKEI_QA_PATH . 'resources/lang', 'qa');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_QA_PATH')) {
            define('RIKKEI_QA_PATH', __DIR__ . '/../');
        }
        $providers = [
            // DatabaseServiceProvider::class,
            RouteServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}
