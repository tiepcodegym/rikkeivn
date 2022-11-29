<?php

namespace Rikkei\ManageTime\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'Rikkei\ManageTime\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot(Router $router)
    {
        parent::boot($router);
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map(Router $router)
    {
        $this->mapWebRoutes($router);

        $this->mapApiRoutes($router);
        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    protected function mapWebRoutes(Router $router)
    {
        $router->group([
            'namespace'  => $this->namespace,
            'middleware' => 'web',
            'as'         => 'manage_time::',
        ], function ($router) {
            require RIKKEI_MANAGETIME_PATH . 'config/routes.php';
        });
    }

    protected function mapApiRoutes(Router $router)
    {
        $router->group([
            'prefix' => 'api',
            'namespace'  => $this->namespace . '\Api',
            'middleware' => 'api',
            'as'         => 'manage_time::api.'
        ], function ($router) {
            require RIKKEI_MANAGETIME_PATH . 'config/routes-api.php';
        });
    }
}
