<?php

namespace Rikkei\TestOld;

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
        $this->loadViewsFrom( RIKKEI_TEST_OLD_PATH . 'resources/views', 'test_old');
        $this->loadTranslationsFrom( RIKKEI_TEST_OLD_PATH . 'resources/lang', 'test_old');
        require __DIR__. '/helper.php';
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_TEST_OLD_PATH')) {
            define('RIKKEI_TEST_OLD_PATH', __DIR__ . '/../');
        }
        $providers = [
            \Rikkei\TestOld\Providers\RouteServiceProvider::class,
            \Rikkei\TestOld\Providers\DatabaseServiceProvider::class,
        ];
        
        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}
