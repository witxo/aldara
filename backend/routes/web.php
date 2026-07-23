<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['guest'])->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register']);
    Route::get('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/tenant/select', [\App\Http\Controllers\Tenant\TenantSwitchController::class, 'showSelector'])->name('tenant.select');
    Route::post('/tenant/switch', [\App\Http\Controllers\Tenant\TenantSwitchController::class, 'switch'])->name('tenant.switch');

    Route::middleware(['tenant.scope'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('/properties', \App\Http\Controllers\Property\PropertyController::class);
        Route::resource('/reservations', \App\Http\Controllers\Reservation\ReservationController::class);
        Route::resource('/guests', \App\Http\Controllers\Guest\GuestController::class);

        Route::prefix('/checkins')->name('checkins.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Checkin\CheckinController::class, 'index'])->name('index');
            Route::get('/{checkin}', [\App\Http\Controllers\Checkin\CheckinController::class, 'show'])->name('show');
            Route::post('/{checkin}/verify', [\App\Http\Controllers\Checkin\CheckinController::class, 'verify'])->name('verify')->middleware('can:verify,checkin');
        });

        Route::get('/integrations', [\App\Http\Controllers\Integration\IntegrationController::class, 'index'])->name('integrations.index');
        Route::post('/integrations/ics/import', [\App\Http\Controllers\Integration\IntegrationController::class, 'importIcs'])->name('integrations.ics.import');
        Route::post('/integrations/calendar/store', [\App\Http\Controllers\Integration\IntegrationController::class, 'storeCalendar'])->name('integrations.calendar.store');
        Route::put('/integrations/calendar/{calendar}', [\App\Http\Controllers\Integration\IntegrationController::class, 'updateCalendar'])->name('integrations.calendar.update');
        Route::delete('/integrations/calendar/{calendar}', [\App\Http\Controllers\Integration\IntegrationController::class, 'destroyCalendar'])->name('integrations.calendar.destroy');
        Route::post('/integrations/calendar/{calendar}/sync', [\App\Http\Controllers\Integration\IntegrationController::class, 'syncCalendar'])->name('integrations.calendar.sync');

        Route::prefix('/ses')->name('ses.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Compliance\SesController::class, 'index'])->name('index');
            Route::get('/{submission}', [\App\Http\Controllers\Compliance\SesController::class, 'show'])->name('show');
            Route::post('/prepare/{reservation}', [\App\Http\Controllers\Compliance\SesController::class, 'prepare'])->name('prepare');
            Route::post('/{submission}/send', [\App\Http\Controllers\Compliance\SesController::class, 'send'])->name('send');
            Route::get('/export', [\App\Http\Controllers\Compliance\SesController::class, 'export'])->name('export');
        });

        Route::prefix('/billing')->name('billing.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Billing\BillingController::class, 'index'])->name('index');
            Route::get('/invoices', [\App\Http\Controllers\Billing\BillingController::class, 'invoices'])->name('invoices');
            Route::get('/change-plan', [\App\Http\Controllers\Billing\BillingController::class, 'changePlan'])->name('change-plan');
            Route::post('/change-plan', [\App\Http\Controllers\Billing\BillingController::class, 'updatePlan']);
        });

        Route::resource('/users', \App\Http\Controllers\Tenant\TenantUserController::class)->names('tenant-users');
        Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/mrz/parse', [\App\Http\Controllers\MrzController::class, 'parse'])->name('settings.mrz.parse');
        Route::get('/properties/{property}/test-ses', [\App\Http\Controllers\Property\PropertyController::class, 'testSes'])->name('properties.test-ses');
        Route::get('/audit', [\App\Http\Controllers\AuditController::class, 'index'])->name('audit.index');
        Route::get('/activity', [\App\Http\Controllers\AuditController::class, 'activity'])->name('activity.index');
    });
});

Route::prefix('/checkin')->name('public.checkin.')->group(function () {
    Route::get('/{token}', [\App\Http\Controllers\PublicCheckinController::class, 'show'])->name('show');
    Route::post('/{token}', [\App\Http\Controllers\PublicCheckinController::class, 'submit'])->name('submit');
});
