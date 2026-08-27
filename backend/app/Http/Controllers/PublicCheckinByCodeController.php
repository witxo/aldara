<?php

namespace App\Http\Controllers;

use App\Domains\Property\Models\Property;
use App\Domains\Reservation\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PublicCheckinByCodeController extends Controller
{
    public function searchForm(string $code)
    {
        $property = Property::where('checkin_code', $code)->first();

        if (!$property) {
            return view('public.checkin-search', ['error' => 'Código de alojamiento no válido.']);
        }

        return view('public.checkin-search', compact('property'));
    }

    public function search(Request $request, string $code)
    {
        $property = Property::where('checkin_code', $code)->first();

        if (!$property) {
            return view('public.checkin-search', ['error' => 'Código de alojamiento no válido.']);
        }

        $validated = $request->validate([
            'checkin' => 'required|date',
            'checkout' => 'required|date|after:checkin',
            'recaptcha_token' => 'required|recaptcha_v3:checkin_search',
        ], [
            'checkin.required' => 'Introduce la fecha de entrada.',
            'checkin.date' => 'La fecha de entrada no es válida.',
            'checkout.required' => 'Introduce la fecha de salida.',
            'checkout.date' => 'La fecha de salida no es válida.',
            'checkout.after' => 'La fecha de salida debe ser posterior a la de entrada.',
        ]);

        $reservations = Reservation::where('property_id', $property->id)
            ->where('checkin_date', $validated['checkin'])
            ->where('checkout_date', $validated['checkout'])
            ->whereNotIn('status', ['cancelled'])
            ->with('mainGuest')
            ->get();

        if ($reservations->isEmpty()) {
            return back()->withInput()->withErrors(['not_found' => 'No encontramos ninguna reserva con esas fechas en ' . $property->name . '. Verifica los datos e inténtalo de nuevo.']);
        }

        if ($reservations->count() > 1) {
            return view('public.checkin-search', [
                'multiple' => true,
                'reservations' => $reservations,
                'property' => $property,
            ]);
        }

        $reservation = $reservations->first();

        if (!$reservation->checkin_token) {
            $reservation->generateCheckinToken();
        }

        return redirect()->route('public.checkin.show', ['token' => $reservation->checkin_token]);
    }

    public function lookup(string $code, string $checkin, string $checkout)
    {
        $property = Property::where('checkin_code', $code)->first();

        if (!$property) {
            return view('public.checkin-by-code', ['error' => 'Código de alojamiento no válido.']);
        }

        $checkinDate = $this->parseDate($checkin);
        $checkoutDate = $this->parseDate($checkout);

        if (!$checkinDate || !$checkoutDate) {
            return view('public.checkin-by-code', [
                'error' => 'No pudimos interpretar las fechas. Asegúrate de que las fechas en el enlace son correctas.',
                'property' => $property,
                'checkin' => $checkin,
                'checkout' => $checkout,
            ]);
        }

        $reservations = Reservation::where('property_id', $property->id)
            ->where('checkin_date', $checkinDate->toDateString())
            ->where('checkout_date', $checkoutDate->toDateString())
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

    private function parseDate(string $raw): ?Carbon
    {
        $cleaned = urldecode($raw);
        $cleaned = trim($cleaned);

        $dayNames = [
            'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday',
            'lunes', 'martes', 'miércoles', 'miercoles', 'jueves', 'viernes', 'sábado', 'sabado', 'domingo',
        ];

        $cleaned = str_ireplace($dayNames, '', $cleaned);
        $cleaned = preg_replace('/[,.]/', ' ', $cleaned);
        $cleaned = preg_replace('/\b(de|del|el|la|los|las|of|the)\b/i', ' ', $cleaned);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        $cleaned = trim($cleaned);

        $spanishMonths = [
            'enero' => 'january',
            'febrero' => 'february',
            'marzo' => 'march',
            'abril' => 'april',
            'mayo' => 'may',
            'junio' => 'june',
            'julio' => 'july',
            'agosto' => 'august',
            'septiembre' => 'september',
            'setiembre' => 'september',
            'octubre' => 'october',
            'noviembre' => 'november',
            'diciembre' => 'december',
        ];

        $cleaned = str_ireplace(array_keys($spanishMonths), array_values($spanishMonths), $cleaned);

        $formats = [
            'Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d',
            'j F Y', 'j F, Y', 'F j Y', 'F j, Y',
            'd F Y', 'd F, Y',
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $cleaned);
                if ($date) return $date->startOfDay();
            } catch (\Exception $e) {}
        }

        try {
            $date = Carbon::parse($cleaned);
            if ($date) return $date->startOfDay();
        } catch (\Exception $e) {}

        return null;
    }
}
