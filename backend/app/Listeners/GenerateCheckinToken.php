<?php

namespace App\Listeners;

use App\Events\ReservationCreated;

class GenerateCheckinToken
{
    public function handle(ReservationCreated $event): void
    {
        if ($event->reservation->source === 'manual') {
            $event->reservation->generateCheckinToken();
        }
    }
}
