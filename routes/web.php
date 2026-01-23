<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-request', function () {
    sleep(1);
    return 'Request OK';
});

