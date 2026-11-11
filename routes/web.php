<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MenuImageController;
use App\Http\Controllers\Owner\BranchController as OwnerBranchController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Owner\MenuController as OwnerMenuController;
use App\Http\Controllers\Owner\OrderController as OwnerOrderController;
use App\Http\Controllers\Owner\StaffController as OwnerStaffController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\MenuController as StaffMenuController;
use App\Http\Controllers\Staff\OrderController as StaffOrderController;
use App\Http\Controllers\Staff\PosController as StaffPosController;
use App\Http\Controllers\Staff\StockController as StaffStockController;
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

        Route::get('/owner/staff', [OwnerStaffController::class, 'index'])
            ->name('owner.staff.index');
        Route::get('/owner/staff/create', [OwnerStaffController::class, 'create'])
            ->name('owner.staff.create');
        Route::post('/owner/staff', [OwnerStaffController::class, 'store'])
            ->name('owner.staff.store');
        Route::get('/owner/staff/{user}/edit', [OwnerStaffController::class, 'edit'])
            ->name('owner.staff.edit');
        Route::put('/owner/staff/{user}', [OwnerStaffController::class, 'update'])
            ->name('owner.staff.update');
        Route::patch('/owner/staff/{user}/toggle', [OwnerStaffController::class, 'toggle'])
            ->name('owner.staff.toggle');
        Route::post('/owner/staff/{user}/reset-password', [OwnerStaffController::class, 'resetPassword'])
            ->name('owner.staff.reset-password');

        Route::get('/owner/branches', [OwnerBranchController::class, 'index'])
            ->name('owner.branches.index');
        Route::get('/owner/branches/create', [OwnerBranchController::class, 'create'])
            ->name('owner.branches.create');
        Route::post('/owner/branches', [OwnerBranchController::class, 'store'])
            ->name('owner.branches.store');
        Route::get('/owner/branches/{branch}/edit', [OwnerBranchController::class, 'edit'])
            ->name('owner.branches.edit');
        Route::put('/owner/branches/{branch}', [OwnerBranchController::class, 'update'])
            ->name('owner.branches.update');
        Route::patch('/owner/branches/{branch}/toggle', [OwnerBranchController::class, 'toggle'])
            ->name('owner.branches.toggle');

        Route::get('/owner/orders', [OwnerOrderController::class, 'index'])
            ->name('owner.orders.index');
        Route::get('/owner/orders/{order}', [OwnerOrderController::class, 'show'])
            ->name('owner.orders.show');
    });

    Route::middleware('role:staff')->group(function () {
        Route::get('/staff/dashboard', [StaffDashboardController::class, 'index'])
            ->name('staff.dashboard');
        Route::get('/staff/menus', [StaffMenuController::class, 'index'])
            ->name('staff.menus.index');

        Route::get('/staff/pos', [StaffPosController::class, 'index'])
            ->name('staff.pos.index');
        Route::post('/staff/pos', [StaffPosController::class, 'store'])
            ->name('staff.pos.store');
        Route::get('/staff/pos/{order}', [StaffPosController::class, 'show'])
            ->name('staff.pos.show');

        Route::get('/staff/orders', [StaffOrderController::class, 'index'])
            ->name('staff.orders.index');
        Route::get('/staff/orders/{order}', [StaffOrderController::class, 'show'])
            ->name('staff.orders.show');

        Route::post('/staff/orders/{order}/items', [StaffOrderController::class, 'storeItem'])
            ->name('staff.orders.items.store');
        Route::patch('/staff/orders/{order}/items/{item}', [StaffOrderController::class, 'updateItem'])
            ->name('staff.orders.items.update');
        Route::delete('/staff/orders/{order}/items/{item}', [StaffOrderController::class, 'destroyItem'])
            ->name('staff.orders.items.destroy');

        Route::patch('/staff/orders/{order}/discount', [StaffOrderController::class, 'applyDiscount'])
            ->name('staff.orders.discount');
        Route::patch('/staff/orders/{order}/notes', [StaffOrderController::class, 'updateNotes'])
            ->name('staff.orders.notes');
        Route::post('/staff/orders/{order}/pay', [StaffOrderController::class, 'pay'])
            ->name('staff.orders.pay');
        Route::patch('/staff/orders/{order}/status', [StaffOrderController::class, 'updateStatus'])
            ->name('staff.orders.status');
        Route::post('/staff/orders/{order}/cancel', [StaffOrderController::class, 'cancel'])
            ->name('staff.orders.cancel');

        Route::get('/staff/stock', [StaffStockController::class, 'index'])
            ->name('staff.stock.index');
        Route::post('/staff/stock', [StaffStockController::class, 'store'])
            ->name('staff.stock.store');
    });

    Route::get('/media/menus/{menu}/{size}', [MenuImageController::class, 'show'])
        ->whereIn('size', ['image', 'thumb'])
        ->name('media.menus.show');

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
