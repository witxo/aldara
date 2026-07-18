<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    public function boot(): void
    {
        parent::boot();
        Horizon::routeMailNotificationsTo('admin@checkin.local');
        Horizon::routeSlackNotificationsTo('#ops', env('SLACK_HOOK_URL'));
    }

    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user) {
            return $user->is_superadmin;
        });
    }
}
