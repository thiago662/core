<?php

namespace App\Http\Middleware;

use App\Api\ApiMessages;
use Closure;
use Illuminate\Http\Request;

class AdministratorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
            if ( auth('api')->user()->type == 'administrador' )
            {
                return $next($request);
            }else{
                abort(403, "you aren't administrador");
            }
    }
}
