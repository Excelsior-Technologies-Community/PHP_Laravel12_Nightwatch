<?php

namespace App\Http\Middleware;

use App\Models\PerformanceMetric;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TrackPerformance
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $response = null;

        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            $durationMs = (microtime(true) - $startTime) * 1000;
            $memoryMb = (memory_get_peak_usage(true) - $startMemory) / 1048576;

            $this->storeMetric(
                $request,
                $durationMs,
                $memoryMb,
                500
            );

            throw $exception;
        }

        $durationMs = (microtime(true) - $startTime) * 1000;
        $memoryMb = (memory_get_peak_usage(true) - $startMemory) / 1048576;

        $this->storeMetric(
            $request,
            $durationMs,
            $memoryMb,
            $response->getStatusCode()
        );

        return $response;
    }

    private function storeMetric(
        Request $request,
        float $durationMs,
        float $memoryMb,
        int $statusCode
    ): void {
        try {
            /*
             * Do not record the monitoring screens themselves.
             * Otherwise opening the performance dashboard would
             * continuously create monitoring records.
             */
            $path = ltrim($request->path(), '/');

if (
    $path === 'nightwatch/dashboard' ||
    $path === 'nightwatch/logs' ||
    $path === 'nightwatch/performance'
) {
    return;
}

            $slowThreshold = (float) config(
                'performance.slow_threshold_ms',
                1000
            );

            $criticalThreshold = (float) config(
                'performance.critical_threshold_ms',
                3000
            );

            $category = match (true) {
                $durationMs >= $criticalThreshold => 'CRITICAL',
                $durationMs >= $slowThreshold => 'SLOW',
                default => 'FAST',
            };

            PerformanceMetric::create([
                'method' => $request->method(),
                'path' => '/' . $path,
                'route_name' => optional($request->route())->getName(),
                'ip_address' => $request->ip(),
                'status_code' => $statusCode,
                'duration_ms' => round($durationMs, 3),
                'memory_mb' => round($memoryMb, 3),
                'category' => $category,
            ]);
        } catch (Throwable $exception) {
            /*
             * Monitoring must never break the application.
             */
        }
    }
}