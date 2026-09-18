<?php

use App\Http\Controllers\NightwatchMonitoringController;
use App\Http\Controllers\PerformanceController;
use App\Http\Middleware\NightwatchAuthMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Original Nightwatch Test Request
|--------------------------------------------------------------------------
*/

Route::get('/test-request', function () {
    sleep(1);
    return 'Request OK';
});

/*
|--------------------------------------------------------------------------
| Nightwatch Monitoring & Telemetry APM
|--------------------------------------------------------------------------
*/

Route::prefix('nightwatch')->middleware([NightwatchAuthMiddleware::class])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard & Interactive Analytics
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [
        NightwatchMonitoringController::class,
        'dashboard'
    ])->name('nightwatch.dashboard');

    /*
    |--------------------------------------------------------------------------
    | Live Telemetry & Real-Time APIs
    |--------------------------------------------------------------------------
    */

    Route::get('/api/telemetry', [
        NightwatchMonitoringController::class,
        'telemetryApi'
    ])->name('nightwatch.api.telemetry');

    Route::get('/api/live-logs', [
        NightwatchMonitoringController::class,
        'liveLogsApi'
    ])->name('nightwatch.api.live-logs');

    Route::get('/live-stream', [
        NightwatchMonitoringController::class,
        'liveStream'
    ])->name('nightwatch.live-stream');

    /*
    |--------------------------------------------------------------------------
    | Logs & Sensitive Data Masking
    |--------------------------------------------------------------------------
    */

    Route::get('/logs', [
        NightwatchMonitoringController::class,
        'logs'
    ])->name('nightwatch.logs');

    Route::get('/logs/export', [
        NightwatchMonitoringController::class,
        'exportLogs'
    ])->name('nightwatch.logs.export');

    /*
    |--------------------------------------------------------------------------
    | Test Log Events
    |--------------------------------------------------------------------------
    */

    Route::post('/test/info', [
        NightwatchMonitoringController::class,
        'generateInfo'
    ])->name('nightwatch.test.info');

    Route::post('/test/warning', [
        NightwatchMonitoringController::class,
        'generateWarning'
    ])->name('nightwatch.test.warning');

    Route::post('/test/exception', [
        NightwatchMonitoringController::class,
        'generateException'
    ])->name('nightwatch.test.exception');

    Route::post('/test/all', [
        NightwatchMonitoringController::class,
        'generateTestLogs'
    ])->name('nightwatch.test.all');

    /*
    |--------------------------------------------------------------------------
    | Performance APM
    |--------------------------------------------------------------------------
    */

    Route::get('/performance', [
        PerformanceController::class,
        'index'
    ])->name('nightwatch.performance');

    Route::get('/performance/export', [
        PerformanceController::class,
        'export'
    ])->name('nightwatch.performance.export');

    Route::get('/performance/test-slow', [
        PerformanceController::class,
        'testSlow'
    ])->name('nightwatch.performance.test-slow');

    Route::get('/performance/test-critical', [
        PerformanceController::class,
        'testCritical'
    ])->name('nightwatch.performance.test-critical');

    Route::delete('/performance/clear', [
        PerformanceController::class,
        'clear'
    ])->name('nightwatch.performance.clear');
});
