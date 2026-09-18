<?php

namespace App\Http\Controllers;

use App\Models\PerformanceMetric;
use App\Services\ServerTelemetryService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class NightwatchMonitoringController extends Controller
{
    /**
     * Nightwatch dashboard.
     */
    public function dashboard(Request $request)
    {
        $range = $this->normalizeRange(
            $request->input('range', 'all')
        );

        /*
        |--------------------------------------------------------------------------
        | 1. Application Logs with Sensitive Data Masking
        |--------------------------------------------------------------------------
        */

        $logs = collect(
            $this->readLogEntries(
                storage_path('logs/laravel.log')
            )
        );

        $logs = $this->filterLogsByRange($logs, $range);

        $totalLogs = $logs->count();

        $errorLogs = $logs
            ->where('level', 'error')
            ->count();

        $warningLogs = $logs
            ->where('level', 'warning')
            ->count();

        $infoLogs = $logs
            ->where('level', 'info')
            ->count();

        $debugLogs = $logs
            ->where('level', 'debug')
            ->count();

        $recentErrors = $logs
            ->where('level', 'error')
            ->take(5)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | 2. Application Health & Server Telemetry
        |--------------------------------------------------------------------------
        */

        $telemetryService = new ServerTelemetryService();
        $telemetry = $telemetryService->getMetrics();

        $health = [
            'application' => true,
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'nightwatch' => config('nightwatch.enabled', true),
        ];

        $healthCount = collect($health)
            ->filter()
            ->count();

        $healthTotal = count($health);

        /*
        |--------------------------------------------------------------------------
        | 3. Performance Query & Statistics
        |--------------------------------------------------------------------------
        */

        $performanceQuery = PerformanceMetric::query();

        $this->applyDateRange(
            $performanceQuery,
            $range
        );

        $performanceTotal = (clone $performanceQuery)->count();
        $performanceAverage = (clone $performanceQuery)->avg('duration_ms') ?? 0;
        $performanceMax = (clone $performanceQuery)->max('duration_ms') ?? 0;
        $performanceMin = (clone $performanceQuery)->min('duration_ms') ?? 0;

        $slowRequests = (clone $performanceQuery)->where('category', 'SLOW')->count();
        $criticalRequests = (clone $performanceQuery)->where('category', 'CRITICAL')->count();
        $fastRequests = (clone $performanceQuery)->where('category', 'FAST')->count();

        $recentPerformance = (clone $performanceQuery)
            ->latest('created_at')
            ->take(5)
            ->get();

        $topUrls = (clone $performanceQuery)
            ->select(
                'path',
                DB::raw('COUNT(*) as requests'),
                DB::raw('AVG(duration_ms) as avg_duration')
            )
            ->whereNotNull('path')
            ->groupBy('path')
            ->orderByDesc('requests')
            ->take(5)
            ->get();

        $slowestRequests = (clone $performanceQuery)
            ->orderByDesc('duration_ms')
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 4. HTTP Status Distribution & Security / 404/403 Threat Analytics
        |--------------------------------------------------------------------------
        */

        $statusDistribution = [
            '2xx' => (clone $performanceQuery)->whereBetween('status_code', [200, 299])->count(),
            '3xx' => (clone $performanceQuery)->whereBetween('status_code', [300, 399])->count(),
            '4xx' => (clone $performanceQuery)->whereBetween('status_code', [400, 499])->count(),
            '5xx' => (clone $performanceQuery)->whereBetween('status_code', [500, 599])->count(),
        ];

        $statusTotal = array_sum($statusDistribution);

        // Security: Top IPs triggering 4xx / 5xx security anomalies
        $threatIps = (clone $performanceQuery)
            ->select('ip_address', DB::raw('COUNT(*) as total_anomalies'), DB::raw('MAX(status_code) as last_status'))
            ->whereIn('status_code', [401, 403, 404, 429, 500])
            ->whereNotNull('ip_address')
            ->groupBy('ip_address')
            ->orderByDesc('total_anomalies')
            ->take(5)
            ->get();

        // Security: Top exploit / scanned target paths
        $suspiciousPaths = (clone $performanceQuery)
            ->select('path', 'status_code', DB::raw('COUNT(*) as attempts'))
            ->whereIn('status_code', [401, 403, 404, 429])
            ->groupBy('path', 'status_code')
            ->orderByDesc('attempts')
            ->take(5)
            ->get();

        $totalSecurityEvents = (clone $performanceQuery)
            ->whereIn('status_code', [401, 403, 404, 429, 500])
            ->count();

        /*
        |--------------------------------------------------------------------------
        | 5. Chart.js Visual Analytics Data (24h Hourly Trend + Top 10 Slowest Routes)
        |--------------------------------------------------------------------------
        */

        $hourlyData = $this->get24HourAnalytics();
        $top10SlowestRoutes = $this->getTop10SlowestRoutes();

        return view('nightwatch.dashboard', compact(
            'range',
            'totalLogs',
            'errorLogs',
            'warningLogs',
            'infoLogs',
            'debugLogs',
            'recentErrors',
            'health',
            'healthCount',
            'healthTotal',
            'telemetry',
            'performanceTotal',
            'performanceAverage',
            'performanceMax',
            'performanceMin',
            'slowRequests',
            'criticalRequests',
            'fastRequests',
            'recentPerformance',
            'topUrls',
            'slowestRequests',
            'statusDistribution',
            'statusTotal',
            'threatIps',
            'suspiciousPaths',
            'totalSecurityEvents',
            'hourlyData',
            'top10SlowestRoutes'
        ));
    }

    /**
     * API: Live Server Telemetry (for real-time dashboard widget updates).
     */
    public function telemetryApi(): JsonResponse
    {
        $telemetryService = new ServerTelemetryService();
        return response()->json([
            'success' => true,
            'timestamp' => now()->toDateTimeString(),
            'telemetry' => $telemetryService->getMetrics(),
        ]);
    }

    /**
     * API: Live Logs (incremental stream for UI).
     */
    public function liveLogsApi(Request $request): JsonResponse
    {
        $limit = (int) $request->input('limit', 25);
        $logs = array_slice($this->readLogEntries(storage_path('logs/laravel.log')), 0, $limit);

        return response()->json([
            'success' => true,
            'count' => count($logs),
            'timestamp' => now()->toDateTimeString(),
            'logs' => $logs,
        ]);
    }

    /**
     * Dedicated Live Stream View.
     */
    public function liveStream()
    {
        return view('nightwatch.live_stream');
    }

    /**
     * Nightwatch logs page with Sensitive Data Masking.
     */
    public function logs(Request $request)
    {
        $logs = $this->filteredLogEntries($request);

        $search = trim((string) $request->input('search', ''));
        $level = $request->input('level', '');
        $date = $request->input('date', '');
        $range = $this->normalizeRange($request->input('range', 'all'));
        $totalLogs = $logs->count();

        return view(
            'nightwatch.logs',
            compact('logs', 'totalLogs', 'search', 'level', 'date', 'range')
        );
    }

    /**
     * Export application logs as CSV with Sensitive Data Masking.
     */
    public function exportLogs(Request $request): StreamedResponse
    {
        $logs = $this->filteredLogEntries($request);

        $filename = 'nightwatch_logs_' . now()->format('Y_m_d_H_i_s') . '.csv';

        return response()->streamDownload(
            function () use ($logs) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Date & Time', 'Level', 'Message']);

                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log['datetime'] ?? '',
                        strtoupper($log['level'] ?? ''),
                        $log['message'] ?? '',
                    ]);
                }

                fclose($handle);
            },
            $filename,
            ['Content-Type' => 'text/csv']
        );
    }

    /**
     * Sensitive Data Masking: Sanitizes sensitive credentials, tokens, credit cards, and keys.
     */
    private function maskSensitiveData(string $text): string
    {
        // 1. Mask passwords: password=xyz, "password": "xyz"
        $text = preg_replace(
            '/(password|passwd|pwd|pass|secret|api_key|apikey|token|access_token|bearer)\s*(=|:)\s*["\']?([^"\'\s&,;]{3,})["\']?/i',
            '$1$2 [REDACTED]',
            $text
        );

        // 2. Mask Authorization Bearer tokens
        $text = preg_replace(
            '/Bearer\s+([a-zA-Z0-9_\-\.]{15,})/i',
            'Bearer [REDACTED_TOKEN]',
            $text
        );

        // 3. Mask Credit Card patterns (13-16 digits)
        $text = preg_replace(
            '/\b(?:\d{4}[-\s]?){3}\d{4}\b/',
            '****-****-****-****',
            $text
        );

        return $text;
    }

    /**
     * Generate 24-hour analytics for Chart.js.
     */
    private function get24HourAnalytics(): array
    {
        $hours = [];
        $volume = [];
        $latency = [];

        $start = now()->subHours(23)->startOfHour();

        for ($i = 0; $i < 24; $i++) {
            $slotStart = (clone $start)->addHours($i);
            $slotEnd = (clone $slotStart)->endOfHour();

            $hours[] = $slotStart->format('H:i');

            $slotMetrics = PerformanceMetric::whereBetween('created_at', [$slotStart, $slotEnd]);
            $count = (clone $slotMetrics)->count();
            $avgDur = (clone $slotMetrics)->avg('duration_ms') ?? 0;

            $volume[] = $count;
            $latency[] = round($avgDur, 1);
        }

        return [
            'labels' => $hours,
            'volume' => $volume,
            'latency' => $latency,
        ];
    }

    /**
     * Top 10 Slowest Unique Routes for Chart.js.
     */
    private function getTop10SlowestRoutes(): array
    {
        $routes = PerformanceMetric::select(
            'path',
            DB::raw('ROUND(AVG(duration_ms), 1) as avg_duration'),
            DB::raw('ROUND(MAX(duration_ms), 1) as max_duration'),
            DB::raw('COUNT(*) as total_calls')
        )
            ->whereNotNull('path')
            ->groupBy('path')
            ->orderByDesc('avg_duration')
            ->take(10)
            ->get();

        return [
            'labels' => $routes->pluck('path')->toArray(),
            'avg_duration' => $routes->pluck('avg_duration')->toArray(),
            'max_duration' => $routes->pluck('max_duration')->toArray(),
            'total_calls' => $routes->pluck('total_calls')->toArray(),
        ];
    }

    /**
     * Generate information log.
     */
    public function generateInfo()
    {
        Log::info('Nightwatch test information log generated.', [
            'source' => 'Nightwatch Dashboard',
            'timestamp' => now()->toDateTimeString(),
            'mock_token' => 'sk_live_983748293489234823', // Will test masking!
        ]);

        return redirect()->route('nightwatch.dashboard')->with('success', 'Information log generated successfully.');
    }

    /**
     * Generate warning log.
     */
    public function generateWarning()
    {
        Log::warning('Nightwatch test warning log generated.', [
            'source' => 'Nightwatch Dashboard',
            'timestamp' => now()->toDateTimeString(),
        ]);

        return redirect()->route('nightwatch.dashboard')->with('success', 'Warning log generated successfully.');
    }

    /**
     * Generate exception log.
     */
    public function generateException()
    {
        try {
            throw new \Exception('Nightwatch test exception generated.');
        } catch (Throwable $exception) {
            Log::error('Nightwatch test exception generated.', [
                'exception' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
        }

        return redirect()->route('nightwatch.dashboard')->with('success', 'Exception log generated successfully.');
    }

    /**
     * Generate all test logs.
     */
    public function generateTestLogs()
    {
        Log::debug('Nightwatch test debug log generated.');
        Log::info('Nightwatch test information log generated.');
        Log::notice('Nightwatch test notice log generated.');
        Log::warning('Nightwatch test warning log generated.');
        Log::error('Nightwatch test error log generated.');

        return redirect()->route('nightwatch.dashboard')->with('success', 'All test logs generated successfully.');
    }

    /**
     * Check database connection.
     */
    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Check cache.
     */
    private function checkCache(): bool
    {
        try {
            $key = 'nightwatch_health_check';
            Cache::put($key, true, now()->addMinutes(1));
            return Cache::get($key) === true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Check storage.
     */
    private function checkStorage(): bool
    {
        try {
            return Storage::disk('local')->put('nightwatch-health-check.txt', 'Nightwatch health check');
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Read Laravel log entries with Sensitive Data Masking.
     */
    private function readLogEntries(string $logFile): array
    {
        if (!file_exists($logFile)) {
            return [];
        }

        $content = file_get_contents($logFile);

        if ($content === false || trim($content) === '') {
            return [];
        }

        preg_match_all(
            '/^\[(.*?)\]\s+(\w+)\.(\w+):\s*(.*?)(?=\n\[|\z)/ms',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        $logs = [];

        foreach ($matches as $match) {
            $datetime = $match[1] ?? '';
            $level = strtolower($match[3] ?? 'info');
            $rawMessage = trim($match[4] ?? '');

            // Apply Sensitive Data Masking
            $maskedMessage = $this->maskSensitiveData($rawMessage);

            $logs[] = [
                'datetime' => $datetime,
                'date' => substr($datetime, 0, 10),
                'level' => $level,
                'message' => $maskedMessage,
            ];
        }

        return array_reverse($logs);
    }

    /**
     * Filter logs according to search, level, date and range.
     */
    private function filteredLogEntries(Request $request): Collection
    {
        $logs = collect(
            $this->readLogEntries(
                storage_path('logs/laravel.log')
            )
        );

        $search = trim((string) $request->input('search', ''));
        $level = strtolower(trim((string) $request->input('level', '')));
        $date = trim((string) $request->input('date', ''));
        $range = $this->normalizeRange($request->input('range', 'all'));

        if ($search !== '') {
            $needle = strtolower($search);
            $logs = $logs->filter(function ($log) use ($needle) {
                return str_contains(
                    strtolower((string) ($log['message'] ?? '')),
                    $needle
                );
            });
        }

        if ($level !== '') {
            $logs = $logs->filter(function ($log) use ($level) {
                return strtolower((string) ($log['level'] ?? '')) === $level;
            });
        }

        if ($date !== '') {
            $logs = $logs->filter(function ($log) use ($date) {
                return ($log['date'] ?? '') === $date;
            });
        } else {
            $logs = $this->filterLogsByRange($logs, $range);
        }

        return $logs->values();
    }

    /**
     * Filter logs by date range.
     */
    private function filterLogsByRange(Collection $logs, string $range): Collection
    {
        if ($range === 'all') {
            return $logs;
        }

        [$start, $end] = $this->resolveDateRange($range);

        if (!$start || !$end) {
            return $logs;
        }

        $startDate = $start->toDateString();
        $endDate = $end->toDateString();

        return $logs->filter(function ($log) use ($startDate, $endDate) {
            $logDate = $log['date'] ?? '';
            return $logDate >= $startDate && $logDate <= $endDate;
        })->values();
    }

    /**
     * Apply performance date range.
     */
    private function applyDateRange($query, string $range): void
    {
        if ($range === 'all') {
            return;
        }

        [$start, $end] = $this->resolveDateRange($range);

        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }
    }

    /**
     * Resolve selected date range.
     */
    private function resolveDateRange(string $range): array
    {
        $now = now();

        return match ($range) {
            'today' => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            '7' => [
                $now->copy()->subDays(6)->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            '30' => [
                $now->copy()->subDays(29)->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            default => [
                null,
                null,
            ],
        };
    }

    /**
     * Normalize date range.
     */
    private function normalizeRange(?string $range): string
    {
        return in_array($range, ['today', '7', '30', 'all'], true) ? $range : 'all';
    }
}