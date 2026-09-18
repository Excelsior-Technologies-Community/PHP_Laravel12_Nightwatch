<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NightwatchAuthMiddleware
{
    /**
     * Handle an incoming request for Nightwatch Dashboard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requiredKey = config('nightwatch.access_key', env('NIGHTWATCH_ACCESS_KEY'));

        // If no access key is configured, pass through
        if (empty($requiredKey)) {
            return $next($request);
        }

        // Check query parameter ?key=... or header X-Nightwatch-Key
        $providedKey = $request->query('key') ?? $request->header('X-Nightwatch-Key') ?? session('nightwatch_authorized_key');

        if ($providedKey === $requiredKey) {
            session(['nightwatch_authorized_key' => $providedKey]);
            return $next($request);
        }

        // Return 403 unauthorized access response
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Unauthorized Access to Nightwatch Telemetry',
                'message' => 'Please provide a valid access key.',
            ], 403);
        }

        return response()->view('nightwatch.unauthorized', [], 403);
    }
}
