<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role, $permission = null)
    {
        $ignore = false;
        $special_url = false;
        if (strpos($request->url(), "/users/employees/update/documents") !== false || strpos($request->url(), "/users/employees/delete/documents") !== false) {
            $special_url = true;
        }

        if ($special_url && !$request->user()->is_activated && $request->user()->hasRole("employee")) {
            $ignore = false;
        } else if (!$request->user()->is_activated) {
            // activate
            return redirect($request->language . '/activate');
        }

        if ($request->user()->hasRole("developer") || $request->user()->hasRole("admin")) {
            $ignore = true;
        } else if ($role == "employee" && ($request->user()->hasRole("client") || $request->user()->hasRole("schedule") || $request->user()->hasRole("financial"))) {
            $ignore = true;
        } else if ($role == "client" && ($request->user()->hasRole("schedule") || $request->user()->hasRole("financial"))) {
            $ignore = true;
        }

        if (!$request->user()->hasRole($role) && !$ignore) {
            abort(404);
        }

        if ($permission !== null && !$request->user()->can($permission)) {

            abort(404);
        }

        return $next($request);
    }
}
