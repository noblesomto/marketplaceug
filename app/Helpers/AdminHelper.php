<?php

use App\Models\Admin;

if (! function_exists('currentAdmin')) {
    function currentAdmin()
    {
        $adminId = session('admin_id');
        return $adminId ? Admin::find($adminId) : null;
    }
}
