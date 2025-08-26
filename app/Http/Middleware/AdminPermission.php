<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AdminPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        // Get the logged-in admin from session
        $admin = Session::get('admin_id');

        // If not logged in
        if (!$admin) {
            abort(401, 'Unauthorized - No admin session');
        }

        // If super admin, allow everything
        if ($admin->hasRole('super_admin')) {
            return $next($request);
        }

        // If admin does not have required permission
        if (!$admin->hasPermission($permission)) {
            abort(403, 'Forbidden - Missing Permission: '.$permission);
        }

        // Otherwise allow
        return $next($request);
    }
}
