<?php

namespace Rikkei\FinesMoney;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\FinesMoney\Providers\DatabaseServiceProvider;
use Rikkei\FinesMoney\Providers\RouteServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(RIKKEI_FINES_MONEY_PATH . 'resources/views', 'fines_money');
        $this->loadTranslationsFrom(RIKKEI_FINES_MONEY_PATH . 'resources/lang', 'fines_money');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_FINES_MONEY_PATH')) {
            define('RIKKEI_FINES_MONEY_PATH', __DIR__ . '/../');
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
