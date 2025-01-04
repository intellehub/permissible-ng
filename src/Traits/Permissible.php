<?php 

namespace Shahnewaz\PermissibleNg\Traits;

use Shahnewaz\PermissibleNg\Role;

trait Permissible {

    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole($role): bool {
        $roles = $this->roles()->pluck('code')->toArray();
        return in_array($role, $roles);
    }

    public function hasPermission($permission, $arguments = []): bool {
        foreach($this->roles as $role) {
            if($role->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function getPermissionsAttribute (): array
    {
        $permissions = [];
        foreach ($this->roles as $role) {
            $rolePermissions = [];
            foreach ($role->permissions as $permission) {
                $rolePermissions[] = $permission->type.'.'.$permission->name;
            }
            $permissions = array_merge($permissions, $rolePermissions);
        }
        return $permissions;
    }
}