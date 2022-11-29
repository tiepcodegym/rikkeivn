<?php

namespace Rikkei\Project;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\Project\Console\Commands\BlockAccountGitRedmine;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom( RIKKEI_PROJECT_PATH . 'resources/views', 'project');
        $this->loadTranslationsFrom( RIKKEI_PROJECT_PATH . 'resources/lang', 'project');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_PROJECT_PATH')) {
            define('RIKKEI_PROJECT_PATH', __DIR__ . '/../');
        }
        $providers = [
            \Rikkei\Project\Providers\DatabaseServiceProvider::class,
            \Rikkei\Project\Providers\RouteServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
        $this->registerCommands();
    }

    /**
     * register commands
     */
    public function registerCommands()
    {
        $this->commands([
            BlockAccountGitRedmine::class
        ]);
    }
}
