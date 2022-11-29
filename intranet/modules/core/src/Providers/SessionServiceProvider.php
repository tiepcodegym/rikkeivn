<?php

namespace Rikkei\Core\Providers;;

use Illuminate\Support\ServiceProvider;
use Rikkei\Core\Console\Commands\SessionFlush;


class SessionServiceProvider extends ServiceProvider
{
    
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('command.session.flush', function ($app) {
            return new SessionFlush($app['cache']);
        });

        $this->commands('command.session.flush');
    }
}
