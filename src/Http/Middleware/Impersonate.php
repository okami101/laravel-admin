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
        if ($request->hasSession()) {
            if ($id = $request->session()->get('impersonate')) {
                auth()->onceUsingId($id);

                return $next($request);
            }

            return (new Pipeline(app()))->send($request)->through([
                '\App\Http\Middleware\Authenticate:sanctum',
            ])->then(function ($request) use ($next) {
                return $next($request);
            });
        }

        return $next($request);
    }
}
