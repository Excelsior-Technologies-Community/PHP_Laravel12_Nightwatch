<?php

namespace App\Http\Controllers;

use App\Models\PerformanceMetric;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class NightwatchMonitoringController extends Controller
{
    /**
     * Nightwatch monitoring dashboard.
     */
    public function dashboard()
    {
        $logFile = storage_path('logs/laravel.log');

        $logs = $this->readLogEntries($logFile);

        $totalLogs = count($logs);

        $errorLogs = collect($logs)
            ->whereIn('level', ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'])
            ->count();

        $warningLogs = collect($logs)
            ->where('level', 'WARNING')
            ->count();

        $infoLogs = collect($logs)
            ->where('level', 'INFO')
            ->count();

        $debugLogs = collect($logs)
            ->where('level', 'DEBUG')
            ->count();

        $recentErrors = collect($logs)
            ->whereIn('level', ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'])
            ->take(5)
            ->values();

        $health = [
            'application' => true,
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'nightwatch' => (bool) config('nightwatch.enabled'),
        ];

        $healthCount = collect($health)->filter()->count();
        $healthTotal = count($health);

        /*
        |--------------------------------------------------------------------------
        | Performance Statistics
        |--------------------------------------------------------------------------
        */

        $performanceTotal = PerformanceMetric::count();

        $performanceAverage = PerformanceMetric::avg('duration_ms') ?? 0;

        $performanceMaximum = PerformanceMetric::max('duration_ms') ?? 0;

        $performanceSlow = PerformanceMetric::where(
            'category',
            'SLOW'
        )->count();

        $performanceCritical = PerformanceMetric::where(
            'category',
            'CRITICAL'
        )->count();

        $recentPerformance = PerformanceMetric::latest()
            ->take(5)
            ->get();

        return view('nightwatch.dashboard', compact(
            'totalLogs',
            'errorLogs',
            'warningLogs',
            'infoLogs',
            'debugLogs',
            'recentErrors',
            'health',
            'healthCount',
            'healthTotal',
            'performanceTotal',
            'performanceAverage',
            'performanceMaximum',
            'performanceSlow',
            'performanceCritical',
            'recentPerformance'
        ));
    }

    /**
     * Display application logs.
     */
    public function logs()
    {
        $logFile = storage_path('logs/laravel.log');

        $logs = $this->readLogEntries($logFile);

        $search = request('search');
        $level = request('level');
        $date = request('date');

        $filteredLogs = collect($logs);

        if ($search) {
            $search = strtolower($search);

            $filteredLogs = $filteredLogs->filter(function ($log) use ($search) {
                return str_contains(
                    strtolower($log['message']),
                    $search
                );
            });
        }

        if ($level && $level !== 'ALL') {
            $filteredLogs = $filteredLogs->filter(function ($log) use ($level) {
                return $log['level'] === $level;
            });
        }

        if ($date) {
            $filteredLogs = $filteredLogs->filter(function ($log) use ($date) {
                return str_starts_with($log['date'], $date);
            });
        }

        $filteredLogs = $filteredLogs->values();

        return view('nightwatch.logs', [
            'logs' => $filteredLogs,
            'totalLogs' => $filteredLogs->count(),
            'search' => $search,
            'level' => $level,
            'date' => $date,
        ]);
    }

    /**
     * Generate an informational log.
     */
    public function generateInfo()
    {
        Log::info('Nightwatch test information log generated successfully.', [
            'source' => 'Nightwatch Monitoring Dashboard',
            'action' => 'info_test',
            'timestamp' => now()->toDateTimeString(),
        ]);

        return redirect()
            ->route('nightwatch.dashboard')
            ->with('success', 'INFO log generated successfully.');
    }

    /**
     * Generate a warning log.
     */
    public function generateWarning()
    {
        Log::warning('Nightwatch test warning generated.', [
            'source' => 'Nightwatch Monitoring Dashboard',
            'action' => 'warning_test',
            'timestamp' => now()->toDateTimeString(),
        ]);

        return redirect()
            ->route('nightwatch.dashboard')
            ->with('success', 'WARNING log generated successfully.');
    }

    /**
     * Generate a test exception.
     */
    public function generateException()
    {
        try {
            throw new \RuntimeException(
                'Nightwatch test exception: simulated application failure.'
            );
        } catch (Throwable $exception) {
            Log::error('Nightwatch test exception captured.', [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'timestamp' => now()->toDateTimeString(),
            ]);
        }

        return redirect()
            ->route('nightwatch.dashboard')
            ->with(
                'success',
                'Test exception generated and logged successfully.'
            );
    }

    /**
     * Generate multiple monitoring events.
     */
    public function generateTestLogs()
    {
        Log::debug('Nightwatch DEBUG test event.', [
            'source' => 'monitoring_test',
        ]);

        Log::info('Nightwatch INFO test event.', [
            'source' => 'monitoring_test',
        ]);

        Log::warning('Nightwatch WARNING test event.', [
            'source' => 'monitoring_test',
        ]);

        Log::error('Nightwatch ERROR test event.', [
            'source' => 'monitoring_test',
        ]);

        return redirect()
            ->route('nightwatch.dashboard')
            ->with(
                'success',
                'Multiple test monitoring events generated.'
            );
    }

    /**
     * Check database connectivity.
     */
    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable $exception) {
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

            Cache::put($key, true, 10);

            return Cache::get($key) === true;
        } catch (Throwable $exception) {
            return false;
        }
    }

    /**
     * Check storage.
     */
    private function checkStorage(): bool
    {
        try {
            return Storage::disk('local')->put(
                'nightwatch-health-check.txt',
                'Nightwatch storage health check'
            );
        } catch (Throwable $exception) {
            return false;
        }
    }

    /**
     * Read and parse Laravel log file.
     */
    private function readLogEntries(string $file): array
    {
        if (!file_exists($file)) {
            return [];
        }

        $content = file_get_contents($file);

        if (!$content) {
            return [];
        }

        $pattern = '/^\[(.*?)\]\s+(\w+)\.(\w+):\s*(.*?)(?=\n\[|\z)/ms';

        preg_match_all(
            $pattern,
            $content,
            $matches,
            PREG_SET_ORDER
        );

        $logs = [];

        foreach ($matches as $match) {
            $logs[] = [
                'datetime' => $match[1],
                'date' => substr($match[1], 0, 10),
                'level' => strtoupper($match[3]),
                'message' => trim($match[4]),
            ];
        }

        return array_reverse($logs);
    }
}