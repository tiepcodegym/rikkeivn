<?php

namespace Rikkei\Music\Providers;

use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            RIKKEI_MUSIC_PATH . 'database' => database_path(),
        ], 'database');
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