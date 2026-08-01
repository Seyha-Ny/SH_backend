<?php

namespace App\Providers;

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
