<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(auth()->user()->homeRouteName())
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:6,1');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route(auth()->user()->homeRouteName());
    })->name('dashboard');

    Route::middleware('role:owner')->group(function () {
        Route::get('/owner/dashboard', [OwnerDashboardController::class, 'index'])
            ->name('owner.dashboard');
    });

    Route::middleware('role:staff')->group(function () {
        Route::get('/staff/dashboard', [StaffDashboardController::class, 'index'])
            ->name('staff.dashboard');
    });

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
