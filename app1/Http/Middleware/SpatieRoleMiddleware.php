<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponseTraits;
use Closure;
use Illuminate\Http\Request;

class SpatieRoleMiddleware
{
    use ApiResponseTraits;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $role, $guard = null)
    {
        $authGuard = app('auth')->guard($guard);

        $roles = is_array($role)
            ? $role
            : explode('|', $role);

        if ($authGuard->user()->hasAnyRole($roles)) {
            return $next($request);
        }

        return $this->errorResponse(403, "User does not have correct roles.");
    }
}
