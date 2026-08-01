<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class HealthController extends Controller
{
    /**
     * Comprehensive health check for production monitoring.
     *
     * Returns the status of key infrastructure components:
     * - Database connectivity
     * - Cache store (Redis/file/database)
     * - Queue connection
     * - App environment and version
     *
     * @OA\Get(
     *     path="/api/health",
     *     summary="Health check endpoint",
     *     tags={"Health"},
     *     @OA\Response(
     *         response=200,
     *         description="All systems operational",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="ok"),
     *             @OA\Property(property="timestamp", type="string", format="date-time"),
     *             @OA\Property(property="app", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=503,
     *         description="One or more services are degraded"
     *     )
     * )
     */
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
        ];

        $allHealthy = collect($checks)->every(fn ($check) => $check['healthy']);

        $response = [
            'status' => $allHealthy ? 'ok' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'app' => [
                'env' => config('app.env'),
                'debug' => config('app.debug'),
                'name' => config('app.name'),
                'url' => config('app.url'),
                'timezone' => config('app.timezone'),
            ],
            'checks' => $checks,
        ];

        return response()->json($response, $allHealthy ? 200 : 503);
    }

    /**
     * Check database connectivity by running a quick query.
     */
    private function checkDatabase(): array
    {
        $start = microtime(true);
        try {
            DB::connection()->getPdo();
            $driver = DB::connection()->getDriverName();
            $latency = (microtime(true) - $start) * 1000;

            return [
                'healthy' => true,
                'driver' => $driver,
                'latency_ms' => round($latency, 1),
            ];
        } catch (\Throwable $e) {
            return [
                'healthy' => false,
                'driver' => config('database.default'),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache store by writing and reading a test value.
     */
    private function checkCache(): array
    {
        $store = config('cache.default');
        $start = microtime(true);

        try {
            $key = 'health_check_' . uniqid();
            Cache::put($key, true, 1);
            $value = Cache::get($key);
            Cache::forget($key);
            $latency = (microtime(true) - $start) * 1000;

            return [
                'healthy' => $value === true,
                'store' => $store,
                'latency_ms' => round($latency, 1),
            ];
        } catch (\Throwable $e) {
            return [
                'healthy' => false,
                'store' => $store,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check queue connection by pinging the configured driver.
     */
    private function checkQueue(): array
    {
        $connection = config('queue.default');

        try {
            // For database-driven queues, verify the jobs table exists
            if ($connection === 'database') {
                $hasTable = DB::connection(config('queue.connections.database.connection', config('database.default')))
                    ->getSchemaBuilder()
                    ->hasTable(config('queue.connections.database.table', 'jobs'));

                return [
                    'healthy' => $hasTable,
                    'connection' => $connection,
                ];
            }

            // For Redis, check connectivity
            if ($connection === 'redis') {
                try {
                    Queue::connection('redis')->size('default');
                    return [
                        'healthy' => true,
                        'connection' => 'redis',
                    ];
                } catch (\Throwable $e) {
                    return [
                        'healthy' => false,
                        'connection' => 'redis',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            // For sync or other drivers, just report as healthy
            return [
                'healthy' => true,
                'connection' => $connection,
            ];
        } catch (\Throwable $e) {
            return [
                'healthy' => false,
                'connection' => $connection,
                'error' => $e->getMessage(),
            ];
        }
    }
}
