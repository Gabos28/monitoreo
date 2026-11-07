<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ServerController;

Route::get('/', [ServerController::class, 'index'])->name('monitor.index');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->middleware(\App\Http\Middleware\SecurityLoginGuard::class)
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::get('/', [ServerController::class, 'index'])->name('monitor.index');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
