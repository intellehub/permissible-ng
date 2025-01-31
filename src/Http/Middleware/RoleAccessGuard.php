<?php

namespace Shahnewaz\PermissibleNg\Http\Middleware;

use Closure;
use Shahnewaz\PermissibleNg\Role;
use Shahnewaz\PermissibleNg\Exceptions\FeatureNotAllowedException;

class RoleAccessGuard
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {

        $userRoles = auth()->user()->roles()->orderBy('weight', 'asc')->get();
        $passed = $this->checkRole($userRoles, $role);
        
        if ($passed) {
            return $next($request);
        }

        if($request->expectsJson()) {
            abort(403);
        }

        return back()->withInput()->withMessage('You are not authorized to access the specified route/feature.');
    }

    /**
     * Check if user has that role
     * */
    private function checkRole($roles, $requiredRole): bool {
        $requiredRoleObject = Role::getCachedRole($requiredRole);

        if (!$requiredRoleObject) {
            return false;
        }

        $hierarchyEnabled = config('permissible.hierarchy', false);
        return $roles->contains(function($role) use($requiredRoleObject, $hierarchyEnabled) {
            // Exact role match
            if ($role->code === $requiredRoleObject->code || 
                $role->name === $requiredRoleObject->name) {
                return true;
            }
            
            // Weight-based hierarchy check only if enabled
            return $hierarchyEnabled && $role->weight <= $requiredRoleObject->weight;
        });
    }
}
