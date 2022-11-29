<?php

namespace Rikkei\Recruitment;

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
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'recruitment');
        $this->loadTranslationsFrom( __DIR__ . '/../resources/lang', 'recruitment');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_RECRUITMENT_PATH')) {
            define('RIKKEI_RECRUITMENT_PATH', __DIR__ . '/../');
        }
        $providers = [
            \Rikkei\Recruitment\Providers\RouteServiceProvider::class,
            \Rikkei\Recruitment\Providers\DatabaseServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}
