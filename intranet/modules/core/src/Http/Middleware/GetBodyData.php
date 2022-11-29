<?php

namespace Rikkei\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GetBodyData
{
    public function handle(Request $request, Closure $next)
    {
        $bodyData = $request->all();
        app()->instance('bodyData', $bodyData);
        return $next($request);
    }
}
