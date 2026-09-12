<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class SoftwareAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('admin_software')->check()) {
            Auth::shouldUse('admin_software');
            return $next($request);
        }
        if (Auth::guard('employees')->check()) {
            Auth::shouldUse('employees');
            return $next($request);
        }
        // dd("SoftwareAuthMiddleware 24", Auth::user());

        return Redirect::route('software.login');
    }
}
