<?php

namespace Rikkei\Recruitment\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot(Router $router)
    {
        $this->publishes([
            RIKKEI_RECRUITMENT_PATH . 'database' => database_path(),
        ], 'database');
    }
}
