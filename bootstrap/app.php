<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'cache' => \App\Http\Middleware\HttpCache::class,
        ]);

        // There is no separate admin login page: everyone signs in through the
        // unified storefront form. Guests who open a protected web route
        // (e.g. the admin panel) are sent to the storefront auth page.
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            $base = rtrim((string) config('app.frontend_url'), '/');

            return $base . '/auth';
        });

        // Session support for the unified login: the storefront /api/login form
        // establishes a real session (no CSRF — the SPA still authenticates with
        // a bearer token) so admins can open the /admin panel right after.
        $middleware->group('session-only', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
        ]);

        // Logging out must ALWAYS work: if the page's CSRF token is stale (the
        // session was rotated or expired in another tab), a normal submit would
        // 419. Exempting logout from CSRF is the standard remedy — the worst
        // case is a third-party page force-logging the user out, which is a
        // harmless nuisance compared to a broken logout.
        $middleware->validateCsrfTokens(except: [
            'admin/logout',
        ]);

        // Trust CDN proxies (Cloudflare, etc.) so visitor's real IP is used
        $middleware->trustProxies(at: [
            '*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Render custom JSON responses for API exceptions
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Resource not found.',
                ], 404);
            }
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Forbidden. You do not have permission to perform this action.',
                ], 403);
            }
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'An error occurred.',
                ], $e->getStatusCode());
            }
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated. Please provide a valid authentication token.',
                ], 401);
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // Handle throttle/rate-limit exceptions
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Too many requests. Please slow down and try again later.',
                    'retry_after' => $e->getHeaders()['Retry-After'] ?? null,
                ], 429);
            }
        });

        // Catch-all for unhandled exceptions
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (($request->is('api/*') || $request->expectsJson()) && ! config('app.debug')) {
                $statusCode = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException
                    ? $e->getStatusCode()
                    : 500;

                return response()->json([
                    'message' => 'An unexpected error occurred. Please try again later.',
                ], $statusCode);
            }
        });
    })->create();
