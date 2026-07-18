<?php

namespace App\Listeners;

use App\Events\CheckinCompleted;
use App\Domains\Reservation\Models\Reservation;

class ProcessCheckinCompleted
{
    public function handle(CheckinCompleted $event): void
    {
        $reservation = $event->checkin->reservation;

        $cekcedInCount = $reservation->checkins()
            ->whereIn('status', ['completed', 'verified'])
            ->count();

        if ($cekcedInCount > 0) {
            $reservation->update([
                'status' => 'partial',
                'checked_in_at' => now(),
            ]);

            $totalGuests = $reservation->guests()->count();
            if ($totalGuests >= $reservation->total_guests) {
                $reservation->update(['status' => 'completed']);
            }
        }
    }
}
