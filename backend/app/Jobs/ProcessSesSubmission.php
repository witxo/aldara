<?php

namespace App\Jobs;

use App\Domains\Compliance\Models\SesSubmission;
use App\Domains\Compliance\Services\SesService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class ProcessSesSubmission implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        public readonly SesSubmission $submission,
    ) {}

    public function handle(SesService $service): void
    {
        $service->send($this->submission);
    }
}
