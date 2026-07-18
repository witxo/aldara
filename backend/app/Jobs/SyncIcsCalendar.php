<?php

namespace App\Jobs;

use App\Domains\Integration\Models\IcsCalendar;
use App\Domains\Integration\Services\IntegrationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncIcsCalendar implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        public readonly IcsCalendar $calendar,
    ) {}

    public function handle(IntegrationService $service): void
    {
        $result = $service->syncIcsCalendar($this->calendar);

        $this->calendar->update([
            'last_sync_at' => now(),
            'last_sync_status' => $result['success'] ? 'ok' : 'error',
            'last_error' => $result['error'] ?? null,
            'last_sync_count' => $result['imported'] ?? 0,
        ]);

        if (!$result['success']) {
            Log::warning('ICS sync failed', [
                'calendar_id' => $this->calendar->id,
                'url' => $this->calendar->url,
                'error' => $result['error'] ?? 'Unknown',
            ]);
        }
    }
}
