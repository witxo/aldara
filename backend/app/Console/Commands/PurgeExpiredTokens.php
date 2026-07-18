<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domains\Reservation\Models\Reservation;

class PurgeExpiredTokens extends Command
{
    protected $signature = 'checkin:purge-expired-tokens';
    protected $description = 'Purge expired check-in tokens';

    public function handle(): void
    {
        $count = Reservation::whereNotNull('checkin_token')
            ->where('checkin_token_expires_at', '<', now())
            ->update([
                'checkin_token' => null,
                'checkin_token_expires_at' => null,
            ]);

        $this->info("Expired tokens purged: {$count}");
    }
}
