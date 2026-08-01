<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Resolve the authenticated user for either guard:
        // - API requests come in with a Sanctum bearer token (auth:sanctum runs first)
        // - Web requests are authenticated via the session/web guard
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                abort(401, 'Unauthenticated. Please provide a valid authentication token.');
            }

            return redirect('/admin/login');
        }

        $isAdmin = $user->is_admin && in_array($user->role, ['admin', 'super_admin'], true);

        if (! $isAdmin) {
            abort(403, 'Forbidden. Admin access required.');
        }

        return $next($request);
    }
}
