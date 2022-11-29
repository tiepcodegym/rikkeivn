<?php

namespace Rikkei\Core\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;
use Rikkei\Api\Helper\Helper as ApiHelper;
use Carbon\Carbon;

class AccessToken
{
    public function handle($request, Closure $next)
    {
        $bearerToken = trim($request->bearerToken());
        $curRoute = request()->route()->getName();
        if (strpos($curRoute, 'SubscriberNotify::subscriber_notify.') !== false) {
            if (!$bearerToken || $bearerToken !== config('api.token')) {
                return response()->json([
                    'success' => 0,
                    'message' => trans('core::message.Access token invalid!')
                ]);
            }
            return $next($request);
        }
        if (!$bearerToken || $bearerToken !== ApiHelper::getTokenByRouteName($curRoute)) {
            return response()->json([
                'success' => 0,
                'message' => trans('core::message.Access token invalid!')
            ]);
        }

        return $next($request);
    }
}
