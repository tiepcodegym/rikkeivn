<?php

namespace Rikkei\AdminSetting;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\AdminSetting\Providers\RouteServiceProvider;
use Rikkei\AdminSetting\Providers\DatabaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(RIKKEI_ADMIN_SETTING_MONEY_PATH . 'resources/views', 'admin_setting');
        $this->loadTranslationsFrom(RIKKEI_ADMIN_SETTING_MONEY_PATH . 'resources/lang', 'admin_setting');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if (!defined('RIKKEI_ADMIN_SETTING_MONEY_PATH')) {
            define('RIKKEI_ADMIN_SETTING_MONEY_PATH', __DIR__ . '/../');
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
