<?php 

namespace Shahnewaz\PermissibleNg\Traits;

use Shahnewaz\PermissibleNg\Role;

/**
 * Trait Permissible
 * 
 * @package Shahnewaz\PermissibleNg\Traits
 */
trait Permissible {

    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole($role): bool {
        $roles = $this->roles()->pluck('code')->toArray();
        return in_array($role, $roles);
    }

    public function hasPermission($permission, $arguments = []): bool {
        $hierarchyEnabled = config('permissible.hierarchy', false);
        
        if ($hierarchyEnabled) {
            // Get the minimum weight (highest privilege) among user's roles
            $minWeight = $this->roles()->min('weight');
            
            // Check permissions in roles with weight >= minWeight
            return $this->roles()
                ->where('weight', '>=', $minWeight)
                ->get()
                ->contains(function($role) use ($permission) {
                    return $role->hasPermission($permission);
                });
        }
        
        // Default behavior without hierarchy
        foreach($this->roles as $role) {
            if($role->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function getPermissionsAttribute(): array {
        // Cache permissions to avoid repeated calculations
        return cache()->remember("user.{$this->id}.permissions", 3600, function() {
            return $this->roles()
                ->with('permissions')
                ->get()
                ->flatMap(function($role) {
                    return $role->permissions->map(fn($permission) => 
                        $permission->type . '.' . $permission->name
                    );
                })
                ->unique()
                ->values()
                ->all();
        });
    }

    // Add method to clear permissions cache
    public function clearPermissionsCache(): void {
        cache()->forget("user.{$this->id}.permissions");
    }

    /**
     * Bulk check multiple permissions
     * 
     * @param array $permissions
     * @param bool $requireAll
     * @return bool
     */
    public function hasPermissions(array $permissions, bool $requireAll = true): bool
    {
        $callback = fn($permission) => $this->hasPermission($permission);
        return $requireAll 
            ? collect($permissions)->every($callback)
            : collect($permissions)->some($callback);
    }

    /**
     * Check if user has any permission of a specific type
     * 
     * @param string $type
     * @return bool
     */
    public function hasPermissionType($type): bool
    {
        return $this->hasPermission("$type.*");
    }


    /**
     * Check if user has all specified roles
     * 
     * @param array $roles Array of role codes or names
     * @return bool
     */
    public function hasRoles(array $roles): bool
    {
        return collect($roles)->every(fn($role) => $this->hasRole($role));
    }
}