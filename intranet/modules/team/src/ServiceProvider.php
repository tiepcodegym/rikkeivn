<?php

namespace Rikkei\Team;

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
        $this->loadViewsFrom( RIKKEI_TEAM_PATH . 'resources/views', 'team');
        $this->loadTranslationsFrom( RIKKEI_TEAM_PATH . 'resources/lang', 'team');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_TEAM_PATH')) {
            define('RIKKEI_TEAM_PATH', __DIR__ . '/../');
        }
        $providers = [
            \Rikkei\Team\Providers\RouteServiceProvider::class,
            //\Rikkei\Team\Providers\ThemeServiceProvider::class,
            \Rikkei\Team\Providers\DatabaseServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}
