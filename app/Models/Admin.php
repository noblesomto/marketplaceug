<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $fillable = ['admin_id', 'username', 'email','password'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'admin_roles');
    }

    public function hasRole($role)
    {
        return $this->roles->contains('name', $role);
    }

    public function hasPermission($permission)
    {
        foreach ($this->roles as $role) {
            if ($role->permissions->contains('name', $permission)) {
                return true;
            }
        }
        return false;
    }

    public function isSuperAdmin()
    {
        // Option A: always treat the very first admin as super admin
        if ($this->id === 1) {
            return true;
        }

        // Option B: check if the admin has explicit super_admin role
        return $this->hasRole('super_admin');
    }

}

