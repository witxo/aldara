<?php

namespace App\Listeners;

use App\Events\CheckinCompleted;
use App\Mail\AdminCheckinNotification;
use Illuminate\Support\Facades\Mail;

class SendAdminCheckinNotification
{
    public function handle(CheckinCompleted $event): void
    {
        Mail::to('ivan@casahortelano.es')
            ->send(new AdminCheckinNotification($event->checkin));
    }
}
