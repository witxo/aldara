<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\Tenant\Services\TenantService;

class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantService::class, function ($app) {
            return new TenantService();
        });
    }

    public function boot(): void
    {
        //
    }
}
