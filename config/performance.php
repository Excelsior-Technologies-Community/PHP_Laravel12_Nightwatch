<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Slow Request Threshold
    |--------------------------------------------------------------------------
    |
    | Requests taking at least this many milliseconds are marked SLOW.
    |
    */

    'slow_threshold_ms' => env(
        'PERFORMANCE_SLOW_THRESHOLD_MS',
        1000
    ),

    /*
    |--------------------------------------------------------------------------
    | Critical Request Threshold
    |--------------------------------------------------------------------------
    |
    | Requests taking at least this many milliseconds are marked CRITICAL.
    |
    */

    'critical_threshold_ms' => env(
        'PERFORMANCE_CRITICAL_THRESHOLD_MS',
        3000
    ),

];