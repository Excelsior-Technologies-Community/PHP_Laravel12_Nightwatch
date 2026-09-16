<?php

use App\Http\Controllers\NightwatchMonitoringController;
use App\Http\Controllers\PerformanceController;
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
| Nightwatch Monitoring
|--------------------------------------------------------------------------
*/

Route::prefix('nightwatch')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Monitoring Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [
        NightwatchMonitoringController::class,
        'dashboard'
    ])->name('nightwatch.dashboard');

    /*
    |--------------------------------------------------------------------------
    | Log Viewer
    |--------------------------------------------------------------------------
    */

    Route::get('/logs', [
        NightwatchMonitoringController::class,
        'logs'
    ])->name('nightwatch.logs');

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
    | Performance Monitoring
    |--------------------------------------------------------------------------
    */

    Route::get('/performance', [
        PerformanceController::class,
        'index'
    ])->name('nightwatch.performance');

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