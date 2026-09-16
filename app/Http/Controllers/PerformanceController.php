<?php

namespace App\Http\Controllers;

use App\Models\PerformanceMetric;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerformanceController extends Controller
{
    /**
     * Display performance monitoring dashboard.
     */
    public function index(Request $request): View
    {
        $query = PerformanceMetric::query();

        $search = trim((string) $request->input('search'));
        $category = $request->input('category');
        $method = $request->input('method');
        $date = $request->input('date');
        $range = $request->input('range', 'all');
        $status = $request->input('status');
        $sort = $request->input('sort', 'latest');
        $perPage = (int) $request->input('per_page', 15);

        /*
        |--------------------------------------------------------------------------
        | Per Page
        |--------------------------------------------------------------------------
        */

        if (!in_array($perPage, [10, 15, 25, 50], true)) {
            $perPage = 15;
        }

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where(
                    'path',
                    'like',
                    '%' . $search . '%'
                )->orWhere(
                    'route_name',
                    'like',
                    '%' . $search . '%'
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Category
        |--------------------------------------------------------------------------
        */

        if ($category && $category !== 'ALL') {
            $query->where('category', $category);
        }

        /*
        |--------------------------------------------------------------------------
        | HTTP Method
        |--------------------------------------------------------------------------
        */

        if ($method && $method !== 'ALL') {
            $query->where('method', $method);
        }

        /*
        |--------------------------------------------------------------------------
        | Status Code
        |--------------------------------------------------------------------------
        */

        if ($status && $status !== 'ALL') {
            $query->where('status_code', $status);
        }

        /*
        |--------------------------------------------------------------------------
        | Exact Date
        |--------------------------------------------------------------------------
        */

        if ($date) {
            $query->whereDate('created_at', $date);
        }

        /*
        |--------------------------------------------------------------------------
        | Date Range Presets
        |--------------------------------------------------------------------------
        */

        $rangeStart = match ($range) {
            'today' => now()->startOfDay(),
            '7days' => now()->subDays(6)->startOfDay(),
            '30days' => now()->subDays(29)->startOfDay(),
            default => null,
        };

        if ($rangeStart) {
            $query->where(
                'created_at',
                '>=',
                $rangeStart
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $filteredRecordsQuery = clone $query;

        switch ($sort) {
            case 'duration_high':
                $filteredRecordsQuery->orderByDesc('duration_ms');
                break;

            case 'duration_low':
                $filteredRecordsQuery->orderBy('duration_ms');
                break;

            case 'status':
                $filteredRecordsQuery->orderBy('status_code');
                break;

            default:
                $filteredRecordsQuery->latest();
                break;
        }

        $filteredRecords = $filteredRecordsQuery
            ->paginate($perPage)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $statsQuery = clone $query;

        $totalRequests = (clone $statsQuery)->count();

        $averageDuration = (clone $statsQuery)
            ->avg('duration_ms') ?? 0;

        $minimumDuration = (clone $statsQuery)
            ->min('duration_ms') ?? 0;

        $maximumDuration = (clone $statsQuery)
            ->max('duration_ms') ?? 0;

        $slowRequests = (clone $statsQuery)
            ->where('category', 'SLOW')
            ->count();

        $criticalRequests = (clone $statsQuery)
            ->where('category', 'CRITICAL')
            ->count();

        $fastRequests = (clone $statsQuery)
            ->where('category', 'FAST')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Methods
        |--------------------------------------------------------------------------
        */

        $methods = PerformanceMetric::query()
            ->select('method')
            ->distinct()
            ->orderBy('method')
            ->pluck('method');

        /*
        |--------------------------------------------------------------------------
        | Status Codes
        |--------------------------------------------------------------------------
        */

        $statuses = PerformanceMetric::query()
            ->select('status_code')
            ->whereNotNull('status_code')
            ->distinct()
            ->orderBy('status_code')
            ->pluck('status_code');

        return view(
            'nightwatch.performance',
            compact(
                'filteredRecords',
                'search',
                'category',
                'method',
                'date',
                'range',
                'status',
                'sort',
                'perPage',
                'totalRequests',
                'averageDuration',
                'minimumDuration',
                'maximumDuration',
                'fastRequests',
                'slowRequests',
                'criticalRequests',
                'methods',
                'statuses'
            )
        );
    }

    /**
     * Export performance metrics as CSV.
     */
    public function export(Request $request)
    {
        $query = PerformanceMetric::query();

        $search = trim((string) $request->input('search'));
        $category = $request->input('category');
        $method = $request->input('method');
        $date = $request->input('date');
        $range = $request->input('range', 'all');
        $status = $request->input('status');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('path', 'like', '%' . $search . '%')
                    ->orWhere('route_name', 'like', '%' . $search . '%');
            });
        }

        if ($category && $category !== 'ALL') {
            $query->where('category', $category);
        }

        if ($method && $method !== 'ALL') {
            $query->where('method', $method);
        }

        if ($status && $status !== 'ALL') {
            $query->where('status_code', $status);
        }

        if ($date) {
            $query->whereDate('created_at', $date);
        }

        $rangeStart = match ($range) {
            'today' => now()->startOfDay(),
            '7days' => now()->subDays(6)->startOfDay(),
            '30days' => now()->subDays(29)->startOfDay(),
            default => null,
        };

        if ($rangeStart) {
            $query->where(
                'created_at',
                '>=',
                $rangeStart
            );
        }

        $records = $query
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Path',
                'Route',
                'Method',
                'Status Code',
                'Duration (ms)',
                'Memory (MB)',
                'Category',
                'Created At',
            ]);

            foreach ($records as $record) {
                fputcsv($handle, [
                    $record->id,
                    $record->path,
                    $record->route_name ?? 'N/A',
                    $record->method,
                    $record->status_code,
                    $record->duration_ms,
                    $record->memory_mb,
                    $record->category,
                    $record->created_at,
                ]);
            }

            fclose($handle);
        }, 'performance_metrics_' . now()->format('Y_m_d_H_i_s') . '.csv');
    }

    /**
     * Generate a slow request for testing.
     */
    public function testSlow(Request $request): RedirectResponse
    {
        $seconds = (float) $request->input('seconds', 2);

        $seconds = max(1, min($seconds, 5));

        sleep((int) floor($seconds));

        $remainingMicroseconds =
            (int) (($seconds - floor($seconds)) * 1000000);

        if ($remainingMicroseconds > 0) {
            usleep($remainingMicroseconds);
        }

        return redirect()
            ->route('nightwatch.performance')
            ->with(
                'success',
                'Slow request test completed in approximately ' .
                number_format($seconds, 2) .
                ' seconds.'
            );
    }

    /**
     * Generate a critical request for testing.
     */
    public function testCritical(): RedirectResponse
    {
        sleep(4);

        return redirect()
            ->route('nightwatch.performance')
            ->with(
                'success',
                'Critical performance test completed.'
            );
    }

    /**
     * Clear all stored performance metrics.
     */
    public function clear(): RedirectResponse
    {
        PerformanceMetric::truncate();

        return redirect()
            ->route('nightwatch.performance')
            ->with(
                'success',
                'All performance metrics have been cleared.'
            );
    }
}
