<?php

namespace App\Http\Controllers;

use App\Domains\Reservation\Models\Reservation;
use App\Domains\Checkin\Models\Checkin;
use App\Domains\Guest\Models\Guest;
use App\Events\CheckinCompleted;
use Illuminate\Http\Request;

class PublicCheckinController extends Controller
{
    public function show(string $token)
    {
        $reservation = Reservation::where('checkin_token', $token)->first();

        if (!$reservation || !$reservation->isTokenValid()) {
            return view('public.checkin', ['error' => 'El enlace de check-in ha expirado o no es válido.']);
        }

        $settings = current_tenant()?->settings ?? [];
        return view('public.checkin', compact('reservation', 'settings'));
    }

    public function submit(Request $request, string $token)
    {
        $reservation = Reservation::where('checkin_token', $token)->firstOrFail();

        if (!$reservation->isTokenValid()) {
            return redirect()->route('public.checkin.show', ['token' => $token]);
        }

        $adults = max(1, (int) $request->input('adults', $reservation->adults));
        $children = max(0, (int) $request->input('children', $reservation->children));
        $expectedGuests = $adults + $children;

        $guests = array_slice((array) $request->input('guests', []), 0, $expectedGuests);
        $request->merge(['guests' => $guests]);

        $validated = $request->validate([
            'adults' => 'integer|min:1',
            'children' => 'integer|min:0',
            'guests' => 'required|array|min:1',
            'guests.*.first_name' => 'required|string|max:100',
            'guests.*.last_name' => 'required|string|max:100',
            'guests.*.document_type' => 'required|in:dni,nie,passport,other',
            'guests.*.document_number' => 'required|string|max:50',
            'guests.*.document_support' => 'nullable|string|max:20',
            'guests.*.nationality' => 'required|string|size:2',
            'guests.*.birth_date' => 'nullable|date',
            'guests.*.email' => 'nullable|email',
            'guests.*.phone' => 'nullable|string|max:20',
            'guests.*.address_line1' => 'nullable|string|max:200',
            'guests.*.address_city' => 'nullable|string|max:100',
            'guests.*.address_postal_code' => 'nullable|string|max:20',
            'signature_data' => 'nullable|string',
            'consent_legal' => 'accepted',
            'consent_data_retention' => 'accepted',
        ]);

        if ($adults !== (int) $reservation->adults || $children !== (int) $reservation->children) {
            $reservation->updateQuietly([
                'adults' => $adults,
                'children' => $children,
            ]);
        }

        $checkin = Checkin::firstOrCreate(
            ['reservation_id' => $reservation->id, 'type' => 'online'],
            ['tenant_id' => $reservation->tenant_id, 'status' => 'pending']
        );

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
                $guestData['checkin_id'] = $checkin->id;
                Guest::create($guestData);
            }
        }

        $checkin->update([
            'signature_data' => $validated['signature_data'] ?? null,
            'consent_legal' => true,
            'consent_data_retention' => true,
            'consent_marketing' => $request->boolean('consent_marketing'),
            'status' => 'completed',
            'completed_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        event(new CheckinCompleted($checkin));

        return view('public.checkin', ['completed' => true, 'reservation' => $reservation]);
    }
}
