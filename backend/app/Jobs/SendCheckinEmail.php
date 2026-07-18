<?php

namespace App\Jobs;

use App\Domains\Reservation\Models\Reservation;
use App\Notifications\CheckinLinkNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class SendCheckinEmail implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        public readonly Reservation $reservation,
    ) {}

    public function handle(): void
    {
        if (!$this->reservation->guest_email) {
            return;
        }

        Notification::route('mail', $this->reservation->guest_email)
            ->notify(new CheckinLinkNotification($this->reservation));
    }
}
