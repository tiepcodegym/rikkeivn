<?php

namespace Rikkei\Core\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'resource/candidate/insert-intranet'
    ];

    /**
     * Array route skip CSRF check
     * @var array
     */
    private $openRoutes = [
        'resource/candidate/insert-intranet',
        'form/config/send-form'
    ];

    /**
     * Check skip CSRF
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, \Closure $next)
    {
        $listRoutes = $this->openRoutes;
        foreach($listRoutes as $route) {
            if ($request->is($route)) {
                return $next($request);
            }
        }

        return parent::handle($request, $next);
    }
}