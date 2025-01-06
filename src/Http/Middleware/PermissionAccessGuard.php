<?php

namespace Shahnewaz\PermissibleNg\Http\Middleware;

use Closure;
use Shahnewaz\PermissibleNg\Exceptions\FeatureNotAllowedException;

class PermissionAccessGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {
        $permissions = is_array($permission)
            ? $permission
            : explode('|', $permission);

        // User must have all permissions specified.
        $permitted = true;

        foreach ($permissions as $permission) {
            if (!auth()->user()->hasPermission($permission)) {
                $permitted = false;
            }
        }

        if($permitted) {
            return $next($request);
        }

        if($request->expectsJson()) {
            abort(401);
        }
        return back()->withInput()->withMessage('You are not authorized to access the specified feature.');
    }
}
