<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\TenantController;
use App\Http\Controllers\Api\V1\PropertyController;
use App\Http\Controllers\Api\V1\ReservationController;
use App\Http\Controllers\Api\V1\GuestController;
use App\Http\Controllers\Api\V1\CheckinController;
use App\Http\Controllers\Api\V1\IntegrationController;
use App\Http\Controllers\Api\V1\SesController;
use App\Http\Controllers\Api\V1\BillingController;
use App\Http\Controllers\Api\V1\AdminController;

Route::name('api.v1.')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('auth.login');
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot-password');
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset-password');

    Route::get('/public/checkin/{token}', [\App\Http\Controllers\Api\V1\PublicCheckinController::class, 'show'])->name('public.checkin.show');
    Route::post('/public/checkin/{token}', [\App\Http\Controllers\Api\V1\PublicCheckinController::class, 'submit'])->middleware('throttle:public-checkin')->name('public.checkin.submit');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');

        Route::apiResource('/tenants', TenantController::class);
        Route::get('/tenants/{tenant}/users', [TenantController::class, 'users'])->name('tenants.users');
        Route::post('/tenants/{tenant}/users', [TenantController::class, 'inviteUser'])->name('tenants.users.invite');
        Route::delete('/tenants/{tenant}/users/{user}', [TenantController::class, 'removeUser'])->name('tenants.users.remove');

        Route::middleware('tenant.scope')->group(function () {
            Route::apiResource('/properties', PropertyController::class);
            Route::get('/properties/{property}/reservations', [PropertyController::class, 'reservations'])->name('properties.reservations');

            Route::apiResource('/reservations', ReservationController::class);
            Route::post('/reservations/{reservation}/send-checkin', [ReservationController::class, 'sendCheckinLink'])->name('reservations.send-checkin');
            Route::get('/reservations/{reservation}/guests', [ReservationController::class, 'guests'])->name('reservations.guests');

            Route::apiResource('/guests', GuestController::class);

            Route::get('/checkins', [CheckinController::class, 'index'])->name('checkins.index');
            Route::post('/checkins', [CheckinController::class, 'store'])->name('checkins.store');
            Route::get('/checkins/{checkin}', [CheckinController::class, 'show'])->name('checkins.show');
            Route::put('/checkins/{checkin}', [CheckinController::class, 'update'])->name('checkins.update');
            Route::post('/checkins/{checkin}/verify', [CheckinController::class, 'verify'])->name('checkins.verify');
            Route::delete('/checkins/{checkin}', [CheckinController::class, 'destroy'])->name('checkins.destroy');

            Route::get('/integrations', [IntegrationController::class, 'index'])->name('integrations.index');
            Route::post('/integrations/{property}/connect', [IntegrationController::class, 'connect'])->name('integrations.connect');
            Route::post('/integrations/ics/import', [IntegrationController::class, 'importIcs'])->name('integrations.ics.import');
            Route::post('/integrations/calendar', [IntegrationController::class, 'storeCalendar'])->name('integrations.calendar.store');
            Route::put('/integrations/calendar/{calendar}', [IntegrationController::class, 'updateCalendar'])->name('integrations.calendar.update');
            Route::delete('/integrations/calendar/{calendar}', [IntegrationController::class, 'destroyCalendar'])->name('integrations.calendar.destroy');
            Route::post('/integrations/calendar/{calendar}/sync', [IntegrationController::class, 'syncCalendar'])->name('integrations.calendar.sync');

            Route::get('/ses/submissions', [SesController::class, 'index'])->name('ses.index');
            Route::post('/ses/prepare/{reservation}', [SesController::class, 'prepare'])->name('ses.prepare');
            Route::post('/ses/test/{property}', [SesController::class, 'test'])->name('ses.test');
            Route::post('/ses/submissions/{submission}/send', [SesController::class, 'send'])->name('ses.send');
            Route::post('/ses/submissions/{submission}/retry', [SesController::class, 'retry'])->name('ses.retry');
            Route::post('/ses/export', [SesController::class, 'export'])->name('ses.export');

            Route::get('/billing/subscription', [BillingController::class, 'subscription'])->name('billing.subscription');
            Route::post('/billing/subscription/change', [BillingController::class, 'changePlan'])->name('billing.change-plan');
            Route::get('/billing/invoices', [BillingController::class, 'invoices'])->name('billing.invoices');
        });

        Route::middleware('superadmin')->prefix('/admin')->name('admin.')->group(function () {
            Route::get('/tenants', [AdminController::class, 'tenants'])->name('tenants');
            Route::get('/tenants/{tenant}', [AdminController::class, 'tenantDetail'])->name('tenants.detail');
            Route::get('/stats', [AdminController::class, 'stats'])->name('stats');
            Route::get('/logs', [AdminController::class, 'logs'])->name('logs');
        });
    });
});
