<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/setup', [AuthController::class, 'showSetup'])->name('setup');
    Route::post('/setup', [AuthController::class, 'setup'])->middleware('throttle:6,1')->name('setup.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/materials/export', [MaterialController::class, 'export'])->name('materials.export');
    Route::post('/materials/import', [MaterialController::class, 'import'])->name('materials.import');
    Route::resource('materials', MaterialController::class)->except('destroy')->names('materials');
    Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');

    Route::resource('suppliers', SupplierController::class)->except(['show'])->names('suppliers');
    Route::resource('categories', CategoryController::class)->except(['show'])->names('categories');

    Route::get('/lots', [StockController::class, 'lots'])->name('lots.index');
    Route::get('/lots/create', [StockController::class, 'createLot'])->name('lots.create');
    Route::post('/lots', [StockController::class, 'storeLot'])->name('lots.store');
    Route::get('/stock/adjustments/create', [StockController::class, 'adjustments'])->name('adjustments.create');
    Route::post('/stock/adjustments', [StockController::class, 'adjust'])->name('adjustments.store');
    Route::get('/stock/movements', [StockController::class, 'movements'])->name('movements.index');

    Route::resource('purchases', PurchaseOrderController::class)->names('purchases');
    Route::post('/purchases/{purchase}/receive', [PurchaseOrderController::class, 'receive'])->name('purchases.receive');

    Route::resource('production', ProductionController::class)->only(['index', 'create', 'store'])->names('production');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
});
