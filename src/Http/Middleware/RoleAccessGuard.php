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
        $requiredRoleObject = cache()->remember(
            "role.{$requiredRole}", 
            3600, 
            fn() => Role::where('code', $requiredRole)
                        ->orWhere('name', $requiredRole)
                        ->first()
        );

        if (!$requiredRoleObject) {
            return false;
        }

        $hierarchyEnabled = config('permissible.hierarchy', true);
        return $roles->contains(function($role) use($requiredRoleObject, $hierarchyEnabled) {
            return $role->code === $requiredRoleObject->code ||
                   $role->name === $requiredRoleObject->name ||
                   ($hierarchyEnabled && $role->weight <= $requiredRoleObject->weight);
        });
    }
}
