<?php

/**
 * Sentry Laravel configuration.
 *
 * @see https://docs.sentry.io/platforms/php/guides/laravel/
 */
return [

    'dsn' => env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')),

    // Capture release version from git tag or env
    'release' => env('SENTRY_RELEASE'),

    // Environment name (production, staging, etc.)
    'environment' => env('APP_ENV', 'production'),

    // Sample rate: 1.0 = send all traces, 0.25 = send 25%
    'sample_rate' => (float) env('SENTRY_SAMPLE_RATE', 1.0),

    // Traces sample rate for performance monitoring
    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.1),

    // Profiles sample rate if you have profiling enabled
    'profiles_sample_rate' => (float) env('SENTRY_PROFILES_SAMPLE_RATE', 0.1),

    // Send default PII (IP address, user id) — disabled by default
    'send_default_pii' => (bool) env('SENTRY_SEND_DEFAULT_PII', false),

    // Attach stack traces to log messages
    'attach_stacktrace' => true,

    // Breadcrumb configuration
    'breadcrumbs' => [
        // Capture SQL queries as breadcrumbs
        'sql_queries' => env('SENTRY_BREADCRUMBS_SQL', true),
        // Capture bindings for SQL queries
        'sql_bindings' => env('SENTRY_BREADCRUMBS_SQL_BINDINGS', false),
        // Capture queue job info
        'queue_info' => env('SENTRY_BREADCRUMBS_QUEUE', true),
        // Capture HTTP client requests
        'http_client_requests' => env('SENTRY_BREADCRUMBS_HTTP_CLIENT', true),
        // Capture cache reads/writes
        'cache_reads' => env('SENTRY_BREADCRUMBS_CACHE', true),
        // Capture log messages
        'logs' => env('SENTRY_BREADCRUMBS_LOGS', true),
    ],

    // Ignore specific exceptions (don't report to Sentry)
    'ignore_exceptions' => [
        // Common HTTP exceptions we handle ourselves
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException::class,
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Validation\ValidationException::class,
        \Illuminate\Http\Exceptions\ThrottleRequestsException::class,
    ],
];
