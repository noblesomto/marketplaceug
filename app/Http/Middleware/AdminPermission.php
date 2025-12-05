<?php
// app/Http/Middleware/AdminPermission.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login')
                ->with('error', 'Please login to access this area.');
        }

        $admin = Auth::guard('admin')->user();

        // Super admin has access to everything
        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        // Check if admin has required permission
        if (!$admin->hasPermissionTo($permission, 'admin')) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
