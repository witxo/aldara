<?php

namespace App\Http\Controllers;

use App\Domains\Property\Models\Property;
use App\Domains\Reservation\Models\Reservation;
use Illuminate\Http\Request;

class PublicCheckinByCodeController extends Controller
{
    public function lookup(string $code, string $checkin, string $checkout)
    {
        $property = Property::where('checkin_code', $code)->first();

        if (!$property) {
            return view('public.checkin-by-code', ['error' => 'Código de alojamiento no válido.']);
        }

        $reservations = Reservation::where('property_id', $property->id)
            ->where('checkin_date', $checkin)
            ->where('checkout_date', $checkout)
            ->whereNotIn('status', ['cancelled'])
            ->with('mainGuest')
            ->get();

        if ($reservations->isEmpty()) {
            return view('public.checkin-by-code', [
                'error' => 'No encontramos ninguna reserva con esas fechas. Verifica los datos o contacta con el alojamiento.',
                'property' => $property,
                'checkin' => $checkin,
                'checkout' => $checkout,
            ]);
        }

        if ($reservations->count() > 1) {
            return view('public.checkin-by-code', [
                'multiple' => true,
                'reservations' => $reservations,
                'property' => $property,
                'checkin' => $checkin,
                'checkout' => $checkout,
            ]);
        }

        $reservation = $reservations->first();

        if (!$reservation->checkin_token) {
            $reservation->generateCheckinToken();
        }

        return redirect()->route('public.checkin.show', ['token' => $reservation->checkin_token]);
    }
}
