<?php

namespace Rikkei\Core\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Rikkei\Core\Http\Middleware\GetLocale::class,
        \Rikkei\Core\Http\Middleware\GetBodyData::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \Rikkei\Core\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Rikkei\Core\Http\Middleware\VerifyCsrfToken::class,
            \Rikkei\Core\Http\Middleware\StoreSessionEmployee::class,
        ],

        'api' => [
            'throttle:60,1',
            'access.token'
        ],
        'api-public' => [
            'throttle:60,1',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Rikkei\Core\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'can' => \Illuminate\Foundation\Http\Middleware\Authorize::class,
        'guest' => \Rikkei\Core\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'logged' => \Rikkei\Core\Http\Middleware\AuthenticateLogged::class,
        'cors' => \Rikkei\Core\Http\Middleware\Cors::class,
        'access.token' => \Rikkei\Core\Http\Middleware\AccessToken::class,
        'localization' => \Rikkei\Core\Http\Middleware\Locale::class,
    ];
}
