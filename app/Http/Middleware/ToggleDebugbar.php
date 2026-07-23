<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ToggleDebugbar
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->bound('debugbar')) {
            if ($request->getHost() === 'dev.belajarcerdas.id') {
                app('debugbar')->enable();
            } else {
                app('debugbar')->disable();
            }
        }

        return $next($request);
    }
}