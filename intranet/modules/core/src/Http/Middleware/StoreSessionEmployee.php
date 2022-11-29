<?php

namespace Rikkei\Core\Http\Middleware;

use Closure;
use Rikkei\Team\Model\Employee;

class StoreSessionEmployee
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (\Auth::check()) {
            $user = \Auth::user();
            $employee = session("employee_{$user->employee_id}");

            if (empty($employee)) {
                $employee = Employee::find($user->employee_id);
                session(["employee_{$user->employee_id}" => $employee]);
            }
        }

        return $next($request);
    }
}