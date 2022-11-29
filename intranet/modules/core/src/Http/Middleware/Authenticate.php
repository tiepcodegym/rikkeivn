<?php

namespace Rikkei\Core\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Session;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\View as CoreView;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            if ($request->ajax() || $request->wantsJson() || CoreUrl::isApi()) {
                return response('Unauthorized.', 401);
            } else {
                Session::put('curUrl',$request->fullUrl());
                return redirect()->guest('/');
            }
        }
        // Check permission
        if (!Permission::getInstance()->isAllow()) {
            return CoreView::viewErrorPermission();
        }
        return $next($request);
    }

}
