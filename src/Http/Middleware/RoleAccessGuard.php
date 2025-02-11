<?php

namespace Shahnewaz\PermissibleNg\Http\Middleware;

use Closure;

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
        $roles = is_array($role)
            ? $role
            : explode('|', $role);

        $user = auth()->user();
        if (!$user) {
            return $this->handleUnauthorized($request);
        }

        if (!collect($roles)->every(fn($r) => $user->hasRole($r))) {
            return $this->handleUnauthorized($request);
        }

        return $next($request);
    }

    private function handleUnauthorized($request) {
        return $request->expectsJson()
            ? response()->json(['message' => 'Unauthorized'], 403)
            : back()->withInput()->withMessage('You are not authorized to access the specified route.');
    }
}
