<?php

namespace Rikkei\SlideShow;

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
        $this->loadViewsFrom( RIKKEI_SLIDESHOW_PATH . 'resources/views', 'slide_show');
        $this->loadTranslationsFrom( RIKKEI_SLIDESHOW_PATH . 'resources/lang', 'slide_show');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_SLIDESHOW_PATH')) {
            define('RIKKEI_SLIDESHOW_PATH', __DIR__ . '/../');
        }
        $providers = [
            \Rikkei\SlideShow\Providers\DatabaseServiceProvider::class,
            \Rikkei\SlideShow\Providers\RouteServiceProvider::class,
            // \Rikkei\SlideShow\Providers\ValidatorServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}
