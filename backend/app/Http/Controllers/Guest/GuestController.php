<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Domains\Guest\Models\Guest;
use App\Domains\Reservation\Models\Reservation;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index()
    {
        $guests = Guest::where('tenant_id', tenant_id())
            ->with('reservation.property')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('panels.guests.index', compact('guests'));
    }

    public function create(Request $request)
    {
        $reservationId = $request->input('reservation_id');
        $reservation = null;
        if ($reservationId) {
            $reservation = Reservation::where('tenant_id', tenant_id())->findOrFail($reservationId);
        }
        $settings = current_tenant()?->settings ?? [];
        return view('panels.guests.form', compact('reservation', 'settings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'document_type' => 'required|in:dni,nie,passport,other',
            'document_number' => 'required|string|max:50',
            'document_support' => 'nullable|string|max:20',
            'nationality' => 'required|string|size:2',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other,unspecified',
            'is_main_guest' => 'nullable|boolean',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'parentesco' => 'nullable|string|max:5',
            'address_line1' => 'nullable|string|max:100',
            'address_line2' => 'nullable|string|max:100',
            'address_city' => 'nullable|string|max:100',
            'address_postal_code' => 'nullable|string|max:20',
            'address_country' => 'nullable|string|size:3',
        ]);

        $validated['tenant_id'] = tenant_id();
        $guest = Guest::create($validated);

        return redirect()->route('reservations.show', $guest->reservation_id)
            ->with('success', 'Huésped añadido correctamente');
    }

    public function show(Guest $guest)
    {
        $this->authorize('view', $guest);
        $guest->load(['reservation.property', 'documents']);
        return view('panels.guests.show', compact('guest'));
    }

    public function edit(Guest $guest)
    {
        $this->authorize('update', $guest);
        $settings = current_tenant()?->settings ?? [];
        return view('panels.guests.form', compact('guest', 'settings'));
    }

    public function update(Request $request, Guest $guest)
    {
        $this->authorize('update', $guest);

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'document_type' => 'sometimes|in:dni,nie,passport,other',
            'document_number' => 'sometimes|string|max:50',
            'document_support' => 'nullable|string|max:20',
            'nationality' => 'sometimes|string|size:2',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other,unspecified',
            'is_main_guest' => 'nullable|boolean',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'parentesco' => 'nullable|string|max:5',
            'address_line1' => 'nullable|string|max:100',
            'address_line2' => 'nullable|string|max:100',
            'address_city' => 'nullable|string|max:100',
            'address_postal_code' => 'nullable|string|max:20',
            'address_country' => 'nullable|string|size:3',
        ]);

        $guest->update($validated);

        return redirect()->route('reservations.show', $guest->reservation_id)
            ->with('success', 'Huésped actualizado correctamente');
    }

    public function destroy(Guest $guest)
    {
        $this->authorize('delete', $guest);
        $guest->delete();

        return redirect()->route('reservations.index')
            ->with('success', 'Huésped eliminado');
    }
}
