<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domains\Compliance\Models\SesSubmission;
use App\Domains\Compliance\Services\SesService;

class SesRetryFailed extends Command
{
    protected $signature = 'checkin:ses-retry-failed';
    protected $description = 'Retry failed SES submissions';

    public function handle(SesService $sesService): void
    {
        $failed = SesSubmission::failed()
            ->where('retry_count', '<', config('ses.retry.max_attempts', 3))
            ->get();

        foreach ($failed as $submission) {
            $sesService->retry($submission);
        }

        $this->info("Retried {$failed->count()} SES submissions");
    }
}
