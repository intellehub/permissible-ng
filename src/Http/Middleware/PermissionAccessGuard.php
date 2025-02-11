<?php

namespace Shahnewaz\PermissibleNg\Http\Middleware;

use Closure;

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

        // More efficient permission checking
        $user = auth()->user();
        if (!$user) {
            return $this->handleUnauthorized($request);
        }

        if (!collect($permissions)->every(fn($perm) => $user->hasPermission($perm))) {
            return $this->handleUnauthorized($request);
        }

        return $next($request);
    }

    private function handleUnauthorized($request) {
        return $request->expectsJson()
            ? response()->json(['message' => 'Unauthorized'], 403)
            : back()->withInput()->withMessage('You are not authorized to access the specified feature.');
    }
}
