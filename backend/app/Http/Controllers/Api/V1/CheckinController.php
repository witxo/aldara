<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domains\Checkin\Models\Checkin;
use App\Domains\Reservation\Models\Reservation;
use App\Events\CheckinCompleted;
use App\Events\CheckinVerified;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    public function index(Request $request)
    {
        $query = Checkin::where('tenant_id', tenant_id());

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('reservation_id')) {
            $query->where('reservation_id', $request->reservation_id);
        }

        if ($request->has('date')) {
            $query->whereHas('reservation', function ($q) use ($request) {
                $q->where('checkin_date', $request->date);
            });
        }

        $checkins = $query->with(['reservation.property'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $checkins->items(),
            'message' => 'Listado de check-ins',
            'status' => 200,
            'meta' => [
                'current_page' => $checkins->currentPage(),
                'per_page' => $checkins->perPage(),
                'total' => $checkins->total(),
                'last_page' => $checkins->lastPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'type' => 'required|in:online,presential',
            'signature_data' => 'nullable|string',
            'consent_legal' => 'nullable|boolean',
            'consent_marketing' => 'nullable|boolean',
            'consent_data_retention' => 'nullable|boolean',
            'guests' => 'nullable|array',
            'guests.*.first_name' => 'required_with:guests|string|max:100',
            'guests.*.last_name' => 'required_with:guests|string|max:100',
            'guests.*.document_type' => 'required_with:guests|string|in:dni,nie,passport,other',
            'guests.*.document_number' => 'required_with:guests|string|max:50',
            'guests.*.nationality' => 'required_with:guests|string|size:2',
        ]);

        $reservation = Reservation::findOrFail($validated['reservation_id']);
        $this->authorize('view', $reservation);

        $checkin = Checkin::create([
            'tenant_id' => tenant_id(),
            'reservation_id' => $reservation->id,
            'type' => $validated['type'],
            'status' => 'pending',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if (!empty($validated['guests'])) {
            foreach ($validated['guests'] as $guestData) {
                $guestData['tenant_id'] = tenant_id();
                $guestData['reservation_id'] = $reservation->id;
                $reservation->guests()->create($guestData);
            }
        }

        if ($validated['signature_data'] ?? false) {
            $checkin->update([
                'signature_data' => $validated['signature_data'],
                'consent_legal' => $validated['consent_legal'] ?? false,
                'consent_marketing' => $validated['consent_marketing'] ?? false,
                'consent_data_retention' => $validated['consent_data_retention'] ?? false,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            event(new CheckinCompleted($checkin));
        }

        return response()->json([
            'data' => $checkin->fresh()->load(['reservation.property', 'reservation.guests']),
            'message' => 'Check-in iniciado',
            'status' => 201,
        ], 201);
    }

    public function show(Checkin $checkin)
    {
        $this->authorize('view', $checkin);
        $checkin->load(['reservation.property', 'reservation.guests', 'guestDocuments']);

        return response()->json([
            'data' => $checkin,
            'message' => 'Detalle del check-in',
            'status' => 200,
        ]);
    }

    public function update(Request $request, Checkin $checkin)
    {
        $this->authorize('update', $checkin);

        $validated = $request->validate([
            'signature_data' => 'nullable|string',
            'consent_legal' => 'nullable|boolean',
            'consent_marketing' => 'nullable|boolean',
            'consent_data_retention' => 'nullable|boolean',
            'status' => 'nullable|in:pending,in_progress,completed,rejected',
            'guest_data' => 'nullable|array',
        ]);

        $checkin->update($validated);

        if ($validated['signature_data'] ?? false) {
            $checkin->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            event(new CheckinCompleted($checkin));
        }

        return response()->json([
            'data' => $checkin->fresh(),
            'message' => 'Check-in actualizado',
            'status' => 200,
        ]);
    }

    public function verify(Request $request, Checkin $checkin)
    {
        $this->authorize('verify', $checkin);

        $validated = $request->validate([
            'status' => 'required|in:verified,rejected',
            'notes' => 'nullable|string',
        ]);

        $checkin->update([
            'status' => $validated['status'],
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
            'metadata' => array_merge($checkin->metadata ?? [], [
                'verification_notes' => $validated['notes'] ?? null,
                'verified_by_user' => $request->user()->name,
            ]),
        ]);

        if ($validated['status'] === 'verified') {
            event(new CheckinVerified($checkin));
        }

        return response()->json([
            'data' => $checkin->fresh(),
            'message' => $checkin->status === 'verified' ? 'Check-in verificado' : 'Check-in rechazado',
            'status' => 200,
        ]);
    }
}
