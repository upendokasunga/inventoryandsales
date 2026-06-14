<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('menu.access')->group(function () {
        Route::resource('users', UserController::class)->only(['index', 'edit', 'update']);
        Route::resource('groups', GroupController::class);
        Route::post('groups/{group}/assign-users', [GroupController::class, 'assignUsers'])->name('groups.assign-users');
        Route::delete('groups/{group}/users/{user}', [GroupController::class, 'removeUser'])->name('groups.remove-user');

        Route::resource('menus', MenuController::class);

        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::patch('settings', [SettingController::class, 'update'])->name('settings.update');

        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        Route::resource('categories', CategoryController::class);
        Route::get('categories-tree', [CategoryController::class, 'tree'])->name('categories.tree');

        Route::resource('units', UnitController::class);

        Route::resource('customer-groups', CustomerGroupController::class);

        Route::resource('suppliers', SupplierController::class);
        Route::get('suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
        Route::get('suppliers-export', [SupplierController::class, 'exportCsv'])->name('suppliers.export-csv');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
