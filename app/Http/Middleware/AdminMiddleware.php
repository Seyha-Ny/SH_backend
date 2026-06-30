<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'super_admin'], true)) {
            return redirect('/admin/login');
        }

        if (auth()->user()->role === 'super_admin') {
            // super_admins pass through all admin routes
        }

        return $next($request);
    }
}
