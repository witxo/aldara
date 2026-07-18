<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domains\Integration\Models\PropertyIntegration;
use App\Domains\Integration\Models\IcsCalendar;
use App\Jobs\SyncIntegrationReservations;
use App\Jobs\SyncIcsCalendar;

class SyncIntegrations extends Command
{
    protected $signature = 'checkin:sync-integrations';
    protected $description = 'Sync reservations from all active integrations and ICS calendars';

    public function handle(): void
    {
        $integrations = PropertyIntegration::where('is_connected', true)->get();

        foreach ($integrations as $integration) {
            if (in_array($integration->provider, ['booking', 'airbnb', 'pms'])) {
                SyncIntegrationReservations::dispatch($integration);
            }
        }

        $calendars = IcsCalendar::where('is_active', true)->get();

        foreach ($calendars as $calendar) {
            SyncIcsCalendar::dispatch($calendar);
        }

        $this->info("Dispatched sync for {$integrations->count()} integrations and {$calendars->count()} ICS calendars");
    }
}
