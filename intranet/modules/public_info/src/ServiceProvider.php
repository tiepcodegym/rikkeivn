<?php

namespace Rikkei\PublicInfo;

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
        $this->loadViewsFrom( RIKKEI_PUBLICINFO_PATH . 'resources/views', 'public_info');
        $this->loadTranslationsFrom( RIKKEI_PUBLICINFO_PATH . 'resources/lang', 'public_info');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_PUBLICINFO_PATH')) {
            define('RIKKEI_PUBLICINFO_PATH', __DIR__ . '/../');
        }
        $providers = [
            \Rikkei\PublicInfo\Providers\RouteServiceProvider::class
        ];
        
        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}
