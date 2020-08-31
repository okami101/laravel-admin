<?php

namespace Vtec\Crud\Http\Middleware;

use Closure;
use Illuminate\Routing\Pipeline;

class Impersonate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->hasSession() && $id = $request->session()->get('impersonate')) {
            auth()->onceUsingId($id);

            return $next($request);
        }

        return $next($request);
    }
}
