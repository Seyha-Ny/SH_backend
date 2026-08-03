<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Per-account login throttle: each email address gets its own bucket,
        // so one user's typo-lockout never blocks other users behind the same
        // NAT/IP, and legitimate retries fit comfortably. Brute-force attempts
        // are still throttled hard per account.
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10)->by($request->input('email') ?: $request->ip());
        });

        $expectedHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        if (!is_string($expectedHost) || $expectedHost === '' || $expectedHost === false) {
            $expectedHost = 'localhost';
        }

        config([
            'sanctum.stateful' => array_values(array_unique(array_merge(
                explode(',', env('SANCTUM_STATEFUL_DOMAINS', '')),
                [$expectedHost],
            ))),
            'sanctum.guard' => ['web'],
            'auth.guards.sanctum' => [
                'driver' => 'sanctum',
                'provider' => 'users',
            ],
        ]);
    }
}
