<?php

namespace Rikkei\Ticket;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\Ticket\Providers\RouteServiceProvider;
use Rikkei\Ticket\Providers\DatabaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(RIKKEI_TICKET_PATH . 'resources/views', 'ticket');
        $this->loadTranslationsFrom(RIKKEI_TICKET_PATH . 'resources/lang', 'ticket');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_TICKET_PATH')) {
            define('RIKKEI_TICKET_PATH', __DIR__ . '/../');
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
