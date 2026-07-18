<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Domains\Compliance\Models\SesSubmission;
use App\Domains\Compliance\Services\SesService;
use App\Domains\Reservation\Models\Reservation;
use Illuminate\Http\Request;

class SesController extends Controller
{
    public function __construct(
        protected SesService $sesService,
    ) {}

    public function index()
    {
        $submissions = SesSubmission::where('tenant_id', tenant_id())
            ->with('reservation.property')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('panels.ses.index', compact('submissions'));
    }

    public function show(SesSubmission $submission)
    {
        $this->authorize('view', $submission);
        return view('panels.ses.show', compact('submission'));
    }

    public function prepare(Reservation $reservation)
    {
        $this->authorize('view', $reservation);
        $checkin = $reservation->checkins()->where('status', 'verified')->first();
        $submission = $this->sesService->prepareSubmission($reservation, $checkin);

        return redirect()->route('ses.show', $submission)->with('success', 'Envío SES preparado');
    }

    public function send(SesSubmission $submission)
    {
        $this->authorize('view', $submission);
        $result = $this->sesService->send($submission);

        $successStatuses = ['sent', 'partially_sent'];
        return redirect()->back()->with(
            in_array($result->status, $successStatuses) ? 'success' : 'error',
            $result->status === 'partially_sent' ? 'Envío parcial: algunos viajeros no se pudieron enviar' : ($result->status === 'sent' ? 'Envío realizado' : 'Error en el envío')
        );
    }

    public function export(Request $request)
    {
        $submissions = SesSubmission::where('tenant_id', tenant_id())
            ->whereIn('status', ['ready', 'sent', 'partially_sent', 'acknowledged', 'failed'])
            ->get();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Domains\Compliance\Exports\SesExport($submissions->pluck('id')->toArray()),
            'ses-submissions.xlsx'
        );
    }
}
