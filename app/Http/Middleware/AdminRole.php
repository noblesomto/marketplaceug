<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Admin;

class AdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  mixed ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle($request, Closure $next, ...$roles)
    {
        $adminId = session('admin_id');
        $admin = $adminId ? Admin::find($adminId) : null;

        if (!$admin) {
            return redirect('/admin')->with('status', [
                'text' => 'Please log in first',
                'type' => 'danger'
            ]);
        }

        // Super admin bypass
        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        // Check roles
        if (! $admin->roles()->whereIn('name', $roles)->exists()) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
