<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('queue:prune-failed')->daily();
        $schedule->command('sanctum:prune-expired')->daily();
        $schedule->command('model:prune')->daily();

        $schedule->command('checkin:purge-expired-tokens')->hourly();
        $schedule->command('checkin:ses-retry-failed')->hourly();
        $schedule->command('checkin:sync-integrations')->everyFifteenMinutes();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
