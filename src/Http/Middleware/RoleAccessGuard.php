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
            abort(401);
        }
        $fallbackRoute = config('permissible.default_fallback_route', 'backend.dashboard');
        return redirect()->route($fallbackRoute)
            ->withMessage('You\'re not authorized to access the specified route/feature.');
    }

    /**
     * Check if user has that role
     * */
    private function checkRole ($roles, $requiredRole) {
        $requiredRoleObject = Role::where('code', $requiredRole)->orWhere('name', $requiredRole)->first();

        if (!$requiredRoleObject) {
            return false;
        }

        // Hierarchy Check
        $hierarchyReached = false;
        $hierarchyEnabled = config('permissible.hierarchy', true);

        foreach ($roles as $role) {
            // We need to pass hierarchy check only once
            if ($role->weight <= $requiredRoleObject->weight) {
                $hierarchyReached = true;
            }

            // If user has exact role, all good
            if ( 
                (
                    ($role->name === $requiredRoleObject->name) 
                    || ($role->code === $requiredRoleObject->code)
                )
                || $hierarchyReached
            ) {
                return true;
            }
        }
        return false;
    }
}
