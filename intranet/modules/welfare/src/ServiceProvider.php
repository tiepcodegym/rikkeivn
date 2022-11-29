<?php

namespace Rikkei\Welfare;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\Welfare\Console\Commands\MailRegister;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
         $this->loadViewsFrom( RIKKEI_WELFARE_PATH . 'resources/views', 'welfare');
        $this->loadTranslationsFrom( RIKKEI_WELFARE_PATH . 'resources/lang', 'welfare');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_WELFARE_PATH')) {
            define('RIKKEI_WELFARE_PATH', __DIR__ . '/../');
        }
        $providers = [
            \Rikkei\Welfare\Providers\RouteServiceProvider::class,
            \Rikkei\Welfare\Providers\DatabaseServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }

        
        $this->registerCommands();
    }

    /**
     * Register the console commands
     */
    private function registerCommands()
    {
        $this->commands([
            MailRegister::class,
        ]);
    }
}
