<?php

namespace Shahnewaz\Permissible\Http\Middleware;

use Closure;
use Shahnewaz\Permissible\Exceptions\FeatureNotAllowedException;

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
        if (auth()->user()->hasPermission($permission)) {
            return $next($request);
        }
        if($request->expectsJson()) {
            abort(401);
        }
        $fallbackRoute = config('permissible.default_fallback_route', 'backend.dashboard');
        return redirect()->route($fallbackRoute)
            ->withMessage('You\'re not authorized to access the specified route/feature.');
    }
}
