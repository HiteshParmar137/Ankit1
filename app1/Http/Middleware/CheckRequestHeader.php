<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTraits;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckRequestHeader
{
    use ApiResponseTraits;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->expectsJson()) {
            // return $this->errorResponse(400, 'Invalid data in header');
            throw new HttpResponseException(
                $this->errorResponse(400, 'Invalid data in header')
            );
        }
        return $next($request);
    }
}
