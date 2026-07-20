<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domains\Reservation\Models\Reservation;
use App\Domains\Property\Models\Property;
use App\Domains\Tenant\Services\TenantService;
use App\Events\ReservationCreated;
use App\Jobs\SendCheckinEmail;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(
        protected TenantService $tenantService,
    ) {}

    public function index(Request $request)
    {
        $query = Reservation::where('tenant_id', tenant_id());

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('source')) {
            $query->where('source', $request->source);
        }

        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->has('checkin_date_from')) {
            $query->whereDate('checkin_date', '>=', $request->checkin_date_from);
        }

        if ($request->has('checkin_date_to')) {
            $query->whereDate('checkin_date', '<=', $request->checkin_date_to);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('guest_name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('external_code', 'like', "%{$search}%");
            });
        }

        $sortField = $request->sort ?? '-created_at';
        $direction = str_starts_with($sortField, '-') ? 'desc' : 'asc';
        $sortField = ltrim($sortField, '-');
        $query->orderBy($sortField, $direction);

        $reservations = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $reservations->items(),
            'message' => 'Listado de reservas',
            'status' => 200,
            'meta' => [
                'current_page' => $reservations->currentPage(),
                'per_page' => $reservations->perPage(),
                'total' => $reservations->total(),
                'last_page' => $reservations->lastPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'nullable|string|max:20',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'infants' => 'nullable|integer|min:0',
            'checkin_date' => 'required|date',
            'checkout_date' => 'required|date|after:checkin_date',
            'checkin_time' => 'nullable|date_format:H:i',
            'checkout_time' => 'nullable|date_format:H:i',
            'total_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
            'source' => 'nullable|in:manual,booking,airbnb,web,pms,other',
        ]);

        $tenant = current_tenant();
        if (!$this->tenantService->canCreateReservation($tenant)) {
            return response()->json([
                'data' => null,
                'message' => 'Límite mensual de reservas alcanzado según su plan',
                'status' => 402,
            ], 402);
        }

        $validated['tenant_id'] = tenant_id();
        $validated['code'] = 'RES-' . strtoupper(substr(md5(uniqid()), 0, 8));
        $validated['status'] = 'confirmed';
        $validated['source'] = $validated['source'] ?? 'manual';
        $validated['adults'] = $validated['adults'] ?? 1;
        $validated['children'] = $validated['children'] ?? 0;
        $validated['infants'] = $validated['infants'] ?? 0;

        $reservation = Reservation::create($validated);

        event(new ReservationCreated($reservation));

        return response()->json([
            'data' => $reservation,
            'message' => 'Reserva creada',
            'status' => 201,
        ], 201);
    }

    public function show(Reservation $reservation)
    {
        $this->authorize('view', $reservation);
        $reservation->load(['property', 'guests', 'checkins', 'sesSubmissions']);

        return response()->json([
            'data' => $reservation,
            'message' => 'Detalle de reserva',
            'status' => 200,
        ]);
    }

    public function update(Request $request, Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        $validated = $request->validate([
            'guest_name' => 'sometimes|string|max:255',
            'guest_email' => 'nullable|email',
            'guest_phone' => 'nullable|string|max:20',
            'adults' => 'sometimes|integer|min:1',
            'children' => 'sometimes|integer|min:0',
            'checkin_date' => 'sometimes|date',
            'checkout_date' => 'sometimes|date|after:checkin_date',
            'status' => 'sometimes|in:pending,confirmed,checkin_sent,partial,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $reservation->update($validated);

        return response()->json([
            'data' => $reservation,
            'message' => 'Reserva actualizada',
            'status' => 200,
        ]);
    }

    public function destroy(Reservation $reservation)
    {
        $this->authorize('delete', $reservation);
        $reservation->delete();

        return response()->json([
            'data' => null,
            'message' => 'Reserva eliminada',
            'status' => 200,
        ]);
    }

    public function sendCheckinLink(Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        $token = $reservation->generateCheckinToken();

        if ($reservation->guest_email) {
            SendCheckinEmail::dispatch($reservation);
        }

        return response()->json([
            'data' => [
                'token' => $token,
                'url' => route('public.checkin.show', ['token' => $token], true),
                'expires_at' => $reservation->checkin_token_expires_at,
            ],
            'message' => 'Enlace de check-in generado',
            'status' => 200,
        ]);
    }

    public function guests(Reservation $reservation)
    {
        $this->authorize('view', $reservation);

        return response()->json([
            'data' => $reservation->guests,
            'message' => 'Huéspedes de la reserva',
            'status' => 200,
        ]);
    }
}
