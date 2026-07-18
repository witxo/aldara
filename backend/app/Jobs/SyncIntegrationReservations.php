<?php

namespace App\Jobs;

use App\Domains\Integration\Models\PropertyIntegration;
use App\Domains\Integration\Services\IntegrationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncIntegrationReservations implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        public readonly PropertyIntegration $integration,
    ) {}

    public function handle(IntegrationService $service): void
    {
        $result = $service->syncReservations($this->integration);

        if (!$result['success']) {
            Log::warning('Sync failed for integration', [
                'integration_id' => $this->integration->id,
                'provider' => $this->integration->provider,
                'error' => $result['error'] ?? 'Unknown',
            ]);
        }
    }
}
