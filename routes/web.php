<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// DEV ONLY: Auto-login route (bypasses authentication)
Route::get('/dev-login', function () {
    $user = \App\Models\User::first();
    Auth::login($user);
    request()->session()->regenerate();
    return redirect()->route($user->landingRoute());
})->name('dev-login');

Route::get('/', fn () => redirect()->route(auth()->check() ? auth()->user()->landingRoute() : 'login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/setup', [AuthController::class, 'showSetup'])->name('setup');
    Route::post('/setup', [AuthController::class, 'setup'])->middleware('throttle:6,1')->name('setup.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::put('/profile', [UserController::class, 'updateProfile'])->middleware('auth')->name('profile.update');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->middleware('permission:dashboard.view')->name('dashboard');

    Route::get('/materials/print', [MaterialController::class, 'print'])->middleware('permission:materials.export')->name('materials.print');
    Route::get('/materials/export', [MaterialController::class, 'export'])->middleware('permission:materials.export')->name('materials.export');
    Route::post('/materials/import', [MaterialController::class, 'import'])->middleware('permission:materials.import')->name('materials.import');
    Route::get('/materials', [MaterialController::class, 'index'])->middleware('permission:materials.view')->name('materials.index');
    Route::get('/materials/create', [MaterialController::class, 'create'])->middleware('permission:materials.create')->name('materials.create');
    Route::post('/materials', [MaterialController::class, 'store'])->middleware('permission:materials.create')->name('materials.store');
    Route::get('/materials/{material}', [MaterialController::class, 'show'])->middleware('permission:materials.view')->name('materials.show');
    Route::get('/materials/{material}/edit', [MaterialController::class, 'edit'])->middleware('permission:materials.edit')->name('materials.edit');
    Route::put('/materials/{material}', [MaterialController::class, 'update'])->middleware('permission:materials.edit')->name('materials.update');
    Route::patch('/materials/{material}', [MaterialController::class, 'update'])->middleware('permission:materials.edit')->name('materials.update.patch');
    Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->middleware('permission:materials.delete')->name('materials.destroy');

    Route::get('/suppliers', [SupplierController::class, 'index'])->middleware('permission:suppliers.view')->name('suppliers.index');
    Route::get('/suppliers/create', [SupplierController::class, 'create'])->middleware('permission:suppliers.create')->name('suppliers.create');
    Route::post('/suppliers', [SupplierController::class, 'store'])->middleware('permission:suppliers.create')->name('suppliers.store');
    Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->middleware('permission:suppliers.edit')->name('suppliers.edit');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->middleware('permission:suppliers.edit')->name('suppliers.update');
    Route::patch('/suppliers/{supplier}', [SupplierController::class, 'update'])->middleware('permission:suppliers.edit')->name('suppliers.update.patch');
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->middleware('permission:suppliers.delete')->name('suppliers.destroy');

    Route::get('/categories', [CategoryController::class, 'index'])->middleware('permission:categories.view')->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->middleware('permission:categories.create')->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->middleware('permission:categories.create')->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->middleware('permission:categories.edit')->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->middleware('permission:categories.edit')->name('categories.update');
    Route::patch('/categories/{category}', [CategoryController::class, 'update'])->middleware('permission:categories.edit')->name('categories.update.patch');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->middleware('permission:categories.delete')->name('categories.destroy');

    Route::get('/lots', [StockController::class, 'lots'])->middleware('permission:lots.view')->name('lots.index');
    Route::get('/lots/create', [StockController::class, 'createLot'])->middleware('permission:lots.create')->name('lots.create');
    Route::post('/lots', [StockController::class, 'storeLot'])->middleware('permission:lots.create')->name('lots.store');
    Route::get('/stock/adjustments/create', [StockController::class, 'adjustments'])->middleware('permission:adjustments.create')->name('adjustments.create');
    Route::post('/stock/adjustments', [StockController::class, 'adjust'])->middleware('permission:adjustments.create')->name('adjustments.store');
    Route::get('/stock/movements', [StockController::class, 'movements'])->middleware('permission:movements.view')->name('movements.index');

    Route::get('/purchases', [PurchaseOrderController::class, 'index'])->middleware('permission:purchases.view')->name('purchases.index');
    Route::get('/purchases/create', [PurchaseOrderController::class, 'create'])->middleware('permission:purchases.create')->name('purchases.create');
    Route::post('/purchases', [PurchaseOrderController::class, 'store'])->middleware('permission:purchases.create')->name('purchases.store');
    Route::get('/purchases/{purchase}', [PurchaseOrderController::class, 'show'])->middleware('permission:purchases.view')->name('purchases.show');
    Route::get('/purchases/{purchase}/edit', [PurchaseOrderController::class, 'edit'])->middleware('permission:purchases.edit')->name('purchases.edit');
    Route::put('/purchases/{purchase}', [PurchaseOrderController::class, 'update'])->middleware('permission:purchases.edit')->name('purchases.update');
    Route::patch('/purchases/{purchase}', [PurchaseOrderController::class, 'update'])->middleware('permission:purchases.edit')->name('purchases.update.patch');
    Route::delete('/purchases/{purchase}', [PurchaseOrderController::class, 'destroy'])->middleware('permission:purchases.delete')->name('purchases.destroy');
    Route::post('/purchases/{purchase}/receive', [PurchaseOrderController::class, 'receive'])->middleware('permission:purchases.receive')->name('purchases.receive');

    Route::get('/production', [ProductionController::class, 'index'])->middleware('permission:production.view')->name('production.index');
    Route::get('/production/create', [ProductionController::class, 'create'])->middleware('permission:production.create')->name('production.create');
    Route::post('/production', [ProductionController::class, 'store'])->middleware('permission:production.create')->name('production.store');

    // Product recipe management
    Route::get('/product-recipes', [App\Http\Controllers\ProductRecipeController::class, 'index'])
        ->middleware('permission:product_recipes.view')
        ->name('product_recipes.index');
    Route::get('/product-recipes/create', [App\Http\Controllers\ProductRecipeController::class, 'create'])
        ->middleware('permission:product_recipes.create')
        ->name('product_recipes.create');
    Route::post('/product-recipes', [App\Http\Controllers\ProductRecipeController::class, 'store'])
        ->middleware('permission:product_recipes.create')
        ->name('product_recipes.store');
    Route::get('/product-recipes/{id}/edit', [App\Http\Controllers\ProductRecipeController::class, 'edit'])
        ->middleware('permission:product_recipes.edit')
        ->name('product_recipes.edit');
    Route::put('/product-recipes/{id}', [App\Http\Controllers\ProductRecipeController::class, 'update'])
        ->middleware('permission:product_recipes.edit')
        ->name('product_recipes.update');
    Route::delete('/product-recipes/{id}', [App\Http\Controllers\ProductRecipeController::class, 'destroy'])
        ->middleware('permission:product_recipes.delete')
        ->name('product_recipes.destroy');

    Route::get('/settings', [SettingsController::class, 'edit'])->middleware('permission:settings.view')->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->middleware('permission:settings.update')->name('settings.update');

    Route::middleware('permission:roles.manage')->prefix('/settings/access/roles')->name('settings.roles.')->controller(RoleController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{role}/edit', 'edit')->name('edit');
        Route::put('/{role}', 'update')->name('update');
        Route::delete('/{role}', 'destroy')->name('destroy');
    });

    Route::middleware('permission:users.manage')->prefix('/settings/access/users')->name('settings.users.')->controller(UserController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{user}/edit', 'edit')->name('edit');
        Route::put('/{user}', 'update')->name('update');
    });
});
