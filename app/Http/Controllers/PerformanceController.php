<?php

namespace App\Http\Controllers;

use App\Models\PerformanceMetric;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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

        if ($date) {
            $query->whereDate('created_at', $date);
        }

        $filteredRecords = (clone $query)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statsQuery = clone $query;

        $totalRequests = (clone $statsQuery)->count();

        $averageDuration = (clone $statsQuery)->avg('duration_ms') ?? 0;

        $minimumDuration = (clone $statsQuery)->min('duration_ms') ?? 0;

        $maximumDuration = (clone $statsQuery)->max('duration_ms') ?? 0;

        $slowRequests = (clone $statsQuery)
            ->where('category', 'SLOW')
            ->count();

        $criticalRequests = (clone $statsQuery)
            ->where('category', 'CRITICAL')
            ->count();

        $fastRequests = (clone $statsQuery)
            ->where('category', 'FAST')
            ->count();

        $methods = PerformanceMetric::query()
            ->select('method')
            ->distinct()
            ->orderBy('method')
            ->pluck('method');

        return view('nightwatch.performance', compact(
            'filteredRecords',
            'search',
            'category',
            'method',
            'date',
            'totalRequests',
            'averageDuration',
            'minimumDuration',
            'maximumDuration',
            'fastRequests',
            'slowRequests',
            'criticalRequests',
            'methods'
        ));
    }

    /**
     * Generate a slow request for testing.
     */
    public function testSlow(Request $request): RedirectResponse
    {
        $seconds = (float) $request->input('seconds', 2);

        $seconds = max(1, min($seconds, 5));

        sleep((int) floor($seconds));

        $remainingMicroseconds = (int) (($seconds - floor($seconds)) * 1000000);

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