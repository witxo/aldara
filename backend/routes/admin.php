<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DatabaseExplorerController;

Route::middleware(['auth', 'superadmin'])->prefix('/admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('/tenants', [AdminController::class, 'tenants'])->name('tenants');
    Route::get('/tenants/create', [AdminController::class, 'createTenant'])->name('tenants.create');
    Route::post('/tenants', [AdminController::class, 'storeTenant'])->name('tenants.store');
    Route::get('/tenants/{tenant}', [AdminController::class, 'showTenant'])->name('tenants.show');
    Route::post('/tenants/{tenant}/toggle', [AdminController::class, 'toggleTenant'])->name('tenants.toggle');
    Route::post('/tenants/{tenant}/plan', [AdminController::class, 'changePlan'])->name('tenants.plan');

    Route::get('/users', [AdminController::class, 'users'])->name('users');

    Route::get('/database', [DatabaseExplorerController::class, 'index'])->name('database');
    Route::get('/database/{table}', [DatabaseExplorerController::class, 'show'])->name('database.table');
    Route::get('/database/{table}/{id}/edit', [DatabaseExplorerController::class, 'edit'])->name('database.edit');
    Route::put('/database/{table}/{id}', [DatabaseExplorerController::class, 'update'])->name('database.update');
    Route::delete('/database/{table}/{id}', [DatabaseExplorerController::class, 'destroy'])->name('database.destroy');
});
