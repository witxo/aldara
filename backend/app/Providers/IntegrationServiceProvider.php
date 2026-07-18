<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\Integration\Connectors\Contracts\ConnectorInterface;
use App\Domains\Integration\Connectors\MockBookingConnector;
use App\Domains\Integration\Connectors\MockAirbnbConnector;
use App\Domains\Integration\Services\IntegrationService;

class IntegrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('connector.booking', function ($app) {
            return new MockBookingConnector();
        });

        $this->app->bind('connector.airbnb', function ($app) {
            return new MockAirbnbConnector();
        });

        $this->app->singleton(IntegrationService::class, function ($app) {
            return new IntegrationService();
        });
    }

    public function boot(): void
    {
        //
    }
}
