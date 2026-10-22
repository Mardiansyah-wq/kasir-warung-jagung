<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MenuImageController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Owner\MenuController as OwnerMenuController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\MenuController as StaffMenuController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(auth()->user()->homeRouteName())
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:6,1');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:6,1');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route(auth()->user()->homeRouteName());
    })->name('dashboard');

    Route::middleware('role:owner')->group(function () {
        Route::get('/owner/dashboard', [OwnerDashboardController::class, 'index'])
            ->name('owner.dashboard');

        Route::get('/owner/menus', [OwnerMenuController::class, 'index'])
            ->name('owner.menus.index');
        Route::get('/owner/menus/create', [OwnerMenuController::class, 'create'])
            ->name('owner.menus.create');
        Route::post('/owner/menus', [OwnerMenuController::class, 'store'])
            ->name('owner.menus.store');
        Route::get('/owner/menus/{menu}/edit', [OwnerMenuController::class, 'edit'])
            ->name('owner.menus.edit');
        Route::put('/owner/menus/{menu}', [OwnerMenuController::class, 'update'])
            ->name('owner.menus.update');
        Route::patch('/owner/menus/{menu}/toggle', [OwnerMenuController::class, 'toggle'])
            ->name('owner.menus.toggle');

        Route::post('/owner/menus/{menu}/variants', [OwnerMenuController::class, 'storeVariant'])
            ->name('owner.menus.variants.store');
        Route::put('/owner/menus/{menu}/variants/{variant}', [OwnerMenuController::class, 'updateVariant'])
            ->name('owner.menus.variants.update');
        Route::patch('/owner/menus/{menu}/variants/{variant}/toggle', [OwnerMenuController::class, 'toggleVariant'])
            ->name('owner.menus.variants.toggle');
    });

    Route::middleware('role:staff')->group(function () {
        Route::get('/staff/dashboard', [StaffDashboardController::class, 'index'])
            ->name('staff.dashboard');
        Route::get('/staff/menus', [StaffMenuController::class, 'index'])
            ->name('staff.menus.index');
    });

    Route::get('/media/menus/{menu}/{size}', [MenuImageController::class, 'show'])
        ->whereIn('size', ['image', 'thumb'])
        ->name('media.menus.show');

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
