<?php

namespace App\Http\Controllers\Api\V1;

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

    public function index(Request $request)
    {
        $query = SesSubmission::where('tenant_id', tenant_id());

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('mode')) {
            $query->where('mode', $request->mode);
        }

        $submissions = $query->with(['reservation.property'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $submissions->items(),
            'message' => 'Listado de envíos SES',
            'status' => 200,
            'meta' => [
                'current_page' => $submissions->currentPage(),
                'per_page' => $submissions->perPage(),
                'total' => $submissions->total(),
                'last_page' => $submissions->lastPage(),
            ],
        ]);
    }

    public function prepare(Reservation $reservation)
    {
        $this->authorize('view', $reservation);

        $checkin = $reservation->checkins()->where('status', 'verified')->first();
        $submission = $this->sesService->prepareSubmission($reservation, $checkin);

        return response()->json([
            'data' => $submission,
            'message' => 'Envío SES preparado. Validar payload antes de enviar.',
            'status' => 201,
        ], 201);
    }

    public function send(SesSubmission $submission)
    {
        $this->authorize('view', $submission);

        $validation = $this->sesService->validatePayload($submission);
        if (!$validation['valid']) {
            return response()->json([
                'data' => [
                    'errors' => $validation['errors'],
                ],
                'message' => 'El payload no es válido. Revise los errores.',
                'status' => 422,
            ], 422);
        }

        $submission->update(['mode' => 'manual']);
        $result = $this->sesService->send($submission);

        $successStatuses = ['sent', 'partially_sent'];

        return response()->json([
            'data' => $result,
            'message' => match ($result->status) {
                'sent' => 'Envío realizado',
                'partially_sent' => 'Envío parcial: algunos viajeros no se pudieron enviar',
                default => 'Error en el envío',
            },
            'status' => in_array($result->status, $successStatuses) ? 200 : 500,
        ]);
    }

    public function retry(SesSubmission $submission)
    {
        $this->authorize('view', $submission);

        $result = $this->sesService->retry($submission);

        return response()->json([
            'data' => $result,
            'message' => 'Reintento de envío ejecutado',
            'status' => 200,
        ]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'submission_ids' => 'required|array',
            'submission_ids.*' => 'exists:ses_submissions,id',
            'format' => 'nullable|in:json,csv',
        ]);

        $format = $request->format ?? 'json';
        $result = $this->sesService->export($request->submission_ids, $format);

        return response()->json([
            'data' => $result,
            'message' => 'Exportación generada',
            'status' => 200,
        ]);
    }
}
