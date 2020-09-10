<?php

namespace Okami101\LaravelAdmin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Locale
{
    public function handle(Request $request, Closure $next)
    {
        app()->setLocale(Str::substr($request->getPreferredLanguage(), 0, 2));

        return $next($request);
    }
}
