<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \App\Domains\Tenant\Models\Tenant::class => \App\Domains\Tenant\Policies\TenantPolicy::class,
        \App\Domains\Property\Models\Property::class => \App\Domains\Property\Policies\PropertyPolicy::class,
        \App\Domains\Reservation\Models\Reservation::class => \App\Domains\Reservation\Policies\ReservationPolicy::class,
        \App\Domains\Guest\Models\Guest::class => \App\Domains\Guest\Policies\GuestPolicy::class,
        \App\Domains\Checkin\Models\Checkin::class => \App\Domains\Checkin\Policies\CheckinPolicy::class,
        \App\Domains\Compliance\Models\SesSubmission::class => \App\Domains\Compliance\Policies\SesSubmissionPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
