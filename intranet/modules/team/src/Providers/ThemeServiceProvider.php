<?php

namespace Rikkei\Team\Providers;

use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //copy data sample lang
        /*$this->publishes([
            RIKKEI_TEAM_PATH . 'data-sample' . DIRECTORY_SEPARATOR . 'lang' => 
                base_path('resources' . DIRECTORY_SEPARATOR . 'lang')
        ], 'assets');*/
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }
}