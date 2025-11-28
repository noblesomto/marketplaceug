<?php
// app/Helpers/AdminHelper.php
namespace App\Helpers;

use App\Models\Admin;
use Illuminate\Support\Facades\Session;

class AdminHelper
{
    /**
     * Get the currently logged-in admin
     *
     * @return Admin|null
     */
    public static function currentAdmin()
    {
        $adminId = Session::get('admin_id');

        if (!$adminId) {
            return null;
        }

        return Admin::find($adminId);
    }

    /**
     * Check if current admin has permission
     *
     * @param string|array $permission
     * @return bool
     */
    public static function can($permission)
    {
        $admin = self::currentAdmin();

        if (!$admin) {
            return false;
        }

        // Super admin can do everything
        if ($admin->isSuperAdmin()) {
            return true;
        }

        // Check permission using Spatie
        if (is_array($permission)) {
            return $admin->hasAnyPermission($permission);
        }

        return $admin->hasPermissionTo($permission, 'admin');
    }

    /**
     * Check if current admin has role
     *
     * @param string|array $role
     * @return bool
     */
    public static function hasRole($role)
    {
        $admin = self::currentAdmin();

        if (!$admin) {
            return false;
        }

        if (is_array($role)) {
            return $admin->hasAnyRole($role);
        }

        return $admin->hasRole($role);
    }
}
