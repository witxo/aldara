<?php

namespace App\Listeners;

use App\Events\CheckinVerified;
use App\Domains\Compliance\Services\SesService;
use App\Jobs\ProcessSesSubmission;

class PrepareSesSubmission
{
    public function __construct(
        protected SesService $sesService,
    ) {}

    public function handle(CheckinVerified $event): void
    {
        if (!config('ses.enabled')) {
            return;
        }

        $submission = $this->sesService->prepareSubmission(
            $event->checkin->reservation,
            $event->checkin,
        );

        if ($submission->status === 'ready') {
            ProcessSesSubmission::dispatch($submission)->onQueue('ses');
        }
    }
}
