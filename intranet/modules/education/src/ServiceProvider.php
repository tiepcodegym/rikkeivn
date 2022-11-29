<?php

namespace Rikkei\Education;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\Education\Providers\DatabaseServiceProvider;
use Rikkei\Education\Providers\RouteServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(RIKKEI_EDUCATION_PATH . 'resources/views', 'education');
        $this->loadTranslationsFrom(RIKKEI_EDUCATION_PATH . 'resources/lang', 'education');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if (!defined('RIKKEI_EDUCATION_PATH')) {
            define('RIKKEI_EDUCATION_PATH', __DIR__ . '/../');
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
