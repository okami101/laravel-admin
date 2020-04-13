<?php

namespace Vtec\Crud\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Locale
{
    public function handle(Request $request, Closure $next)
    {
        app()->setLocale($request->getPreferredLanguage());

        return $next($request);
    }
}
