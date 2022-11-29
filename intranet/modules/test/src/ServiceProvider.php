<?php

namespace Rikkei\Test;

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
        $this->loadViewsFrom( RIKKEI_TEST_PATH . 'resources/views', 'test');
        $this->loadTranslationsFrom( RIKKEI_TEST_PATH . 'resources/lang', 'test');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_TEST_PATH')) {
            define('RIKKEI_TEST_PATH', __DIR__ . '/../');
        }
        require __DIR__. '/helper.php';
        $providers = [
            \Rikkei\Test\Providers\RouteServiceProvider::class,
            \Rikkei\Test\Providers\DatabaseServiceProvider::class,
            \Rikkei\Test\Providers\EventServiceProvider::class,
        ];
        
        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}
