<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domains\Reservation\Models\Reservation;
use App\Domains\Checkin\Models\Checkin;
use App\Domains\Guest\Models\Guest;
use App\Events\CheckinCompleted;
use Illuminate\Http\Request;

class PublicCheckinController extends Controller
{
    public function show(string $token)
    {
        $reservation = Reservation::where('checkin_token', $token)->firstOrFail();

        if (!$reservation->isTokenValid()) {
            return response()->json([
                'data' => null,
                'message' => 'El enlace de check-in ha expirado o no es válido',
                'status' => 410,
            ], 410);
        }

        return response()->json([
            'data' => [
                'reservation' => [
                    'code' => $reservation->code,
                    'guest_name' => $reservation->guest_name,
                    'checkin_date' => $reservation->checkin_date,
                    'checkout_date' => $reservation->checkout_date,
                    'adults' => $reservation->adults,
                    'children' => $reservation->children,
                ],
                'property' => [
                    'name' => $reservation->property->name,
                    'address' => $reservation->property->address_line1,
                    'city' => $reservation->property->city,
                    'license_number' => $reservation->property->license_number,
                ],
                'config' => [
                    'require_signature' => config('checkin.require_signature'),
                    'require_document' => config('checkin.require_document_upload'),
                    'max_guests' => config('checkin.max_guests'),
                ],
            ],
            'message' => 'Datos de reserva para check-in',
            'status' => 200,
        ]);
    }

    public function submit(Request $request, string $token)
    {
        $reservation = Reservation::where('checkin_token', $token)->firstOrFail();

        if (!$reservation->isTokenValid()) {
            return response()->json([
                'data' => null,
                'message' => 'El enlace de check-in ha expirado',
                'status' => 410,
            ], 410);
        }

        $validated = $request->validate([
            'guests' => 'required|array|min:1',
            'guests.*.first_name' => 'required|string|max:100',
            'guests.*.last_name' => 'required|string|max:100',
            'guests.*.document_type' => 'required|in:dni,nie,passport,other',
            'guests.*.document_number' => 'required|string|max:50',
            'guests.*.nationality' => 'required|string|size:2',
            'guests.*.birth_date' => 'nullable|date',
            'guests.*.gender' => 'nullable|in:male,female,other',
            'guests.*.email' => 'nullable|email',
            'guests.*.phone' => 'nullable|string|max:20',
            'signature_data' => 'nullable|string',
            'consent_legal' => 'accepted',
            'consent_marketing' => 'nullable|boolean',
            'consent_data_retention' => 'accepted',
        ]);

        $checkin = Checkin::where('reservation_id', $reservation->id)
            ->where('type', 'online')
            ->first();

        if (!$checkin) {
            $checkin = Checkin::create([
                'tenant_id' => $reservation->tenant_id,
                'reservation_id' => $reservation->id,
                'type' => 'online',
                'status' => 'pending',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        $existingGuests = Guest::where('reservation_id', $reservation->id)->get();

        foreach ($validated['guests'] as $i => $guestData) {
            $guestData['tenant_id'] = $reservation->tenant_id;
            $guestData['reservation_id'] = $reservation->id;
            $guestData['is_main_guest'] = $i === 0;

            $matched = $existingGuests->first(function ($g) use ($guestData) {
                return $g->document_type === $guestData['document_type']
                    && $g->document_number === $guestData['document_number'];
            });

            if ($matched) {
                $matched->update($guestData);
            } else {
                Guest::create($guestData);
            }
        }

        $checkin->update([
            'signature_data' => $validated['signature_data'] ?? null,
            'consent_legal' => true,
            'consent_marketing' => $validated['consent_marketing'] ?? false,
            'consent_data_retention' => true,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        event(new CheckinCompleted($checkin));

        return response()->json([
            'data' => [
                'checkin_id' => $checkin->id,
                'status' => 'completed',
                'message' => 'Check-in completado correctamente',
            ],
            'message' => 'Check-in realizado con éxito',
            'status' => 200,
        ]);
    }
}
