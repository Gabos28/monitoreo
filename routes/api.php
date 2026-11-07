<?php
use App\Http\Controllers\ServerController;
Route::get('/metrics', [ServerController::class, 'api'])
    ->middleware(['web', 'auth', 'throttle:60,1'])
    ->name('api.metrics');

Route::get('/login-attempts', [ServerController::class, 'loginAttempts'])
    ->middleware(['web', 'auth', 'throttle:60,1'])
    ->name('api.login-attempts');