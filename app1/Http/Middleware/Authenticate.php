<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponseTraits;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;

class Authenticate extends Middleware
{
    use ApiResponseTraits;

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */

    /**
     * Overriding handle method
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);
        return $next($request);
    }

    /**
     * Overriding unauthenticated method
     */
    protected function unauthenticated($request, array $guards)
    {
        throw new HttpResponseException(
            $this->errorResponse(401, 'Unauthenticated')
        );
    }
}
