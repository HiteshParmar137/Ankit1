<?php

namespace App\Http\Middleware;

use App\Enums\UserTypes;
use Closure;
use App\Models\Role;
use App\Traits\ApiResponseTraits;

class SpatiePermissionMiddleware
{
    use ApiResponseTraits;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next, $permission, $guard = null)
    {
        $authGuard = app('auth')->guard($guard);

        $permissions = is_array($permission)
            ? $permission
            : explode('|', $permission);

        if ($authGuard->user()->hasAnyDirectPermission($permissions) || $authGuard->user()->hasRole(UserTypes::SUPER_ADMIN->value)) {
            return $next($request);
        };


        return $this->errorResponse(403, "User does not have correct permission.");

        /*
        -> Date : 30/8/22
        foreach ($permissions as $permission) {
            if ($authGuard->user()->can($permission)) {
                return $next($request);
            }
        }

        throw UnauthorizedException::forPermissions($permissions);

        return $next($request);
        */
    }
}
