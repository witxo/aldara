<?php

namespace App\Listeners;

use App\Events\SesSubmissionStatusChanged;
use Illuminate\Support\Facades\Log;

class LogSesStatusChange
{
    public function handle(SesSubmissionStatusChanged $event): void
    {
        Log::channel('stack')->info('SES Submission status changed', [
            'submission_id' => $event->submission->id,
            'status' => $event->submission->status,
            'reference' => $event->submission->reference,
            'reservation_id' => $event->submission->reservation_id,
        ]);
    }
}
