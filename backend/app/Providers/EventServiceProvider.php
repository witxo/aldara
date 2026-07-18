<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \App\Events\CheckinCompleted::class => [
            \App\Listeners\ProcessCheckinCompleted::class,
        ],
        \App\Events\CheckinVerified::class => [
            \App\Listeners\PrepareSesSubmission::class,
        ],
        \App\Events\ReservationCreated::class => [
            \App\Listeners\GenerateCheckinToken::class,
        ],
        \App\Events\SesSubmissionStatusChanged::class => [
            \App\Listeners\LogSesStatusChange::class,
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
