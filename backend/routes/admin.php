<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;

Route::middleware(['auth', 'superadmin'])->prefix('/admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('/tenants', [AdminController::class, 'tenants'])->name('tenants');
    Route::get('/tenants/create', [AdminController::class, 'createTenant'])->name('tenants.create');
    Route::post('/tenants', [AdminController::class, 'storeTenant'])->name('tenants.store');
    Route::get('/tenants/{tenant}', [AdminController::class, 'showTenant'])->name('tenants.show');
    Route::post('/tenants/{tenant}/toggle', [AdminController::class, 'toggleTenant'])->name('tenants.toggle');
    Route::post('/tenants/{tenant}/plan', [AdminController::class, 'changePlan'])->name('tenants.plan');

    Route::get('/users', [AdminController::class, 'users'])->name('users');
});
