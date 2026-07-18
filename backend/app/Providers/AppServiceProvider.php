<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Tinker\TinkerServiceProvider::class);
        }
    }

    public function boot(): void
    {
        Model::shouldBeStrict(!$this->app->isProduction());
        Paginator::useBootstrapFive();

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
