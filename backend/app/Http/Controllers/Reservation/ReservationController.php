<?php

namespace App\Http\Controllers\Reservation;

use App\Http\Controllers\Controller;
use App\Domains\Reservation\Models\Reservation;
use App\Domains\Property\Models\Property;
use App\Events\ReservationCreated;
use App\Jobs\SendCheckinEmail;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservation::where('tenant_id', tenant_id())->with('property');

        if ($request->has('status')) $query->where('status', $request->status);
        if ($request->has('property_id')) $query->where('property_id', $request->property_id);
        if ($request->has('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('guest_name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%");
            });
        }

        $sortable = ['code', 'guest_name', 'checkin_date', 'checkout_date', 'source', 'status', 'created_at'];
        $sort = in_array($request->sort, $sortable) ? $request->sort : 'checkin_date';
        $direction = in_array($request->direction, ['asc', 'desc']) ? $request->direction : 'asc';

        $query->orderBy($sort, $direction);
        $reservations = $query->paginate(20)->appends($request->only(['sort', 'direction', 'search', 'status', 'property_id']));
        $properties = Property::where('tenant_id', tenant_id())->get();

        return view('panels.reservations.index', compact('reservations', 'properties', 'sort', 'direction'));
    }

    public function create()
    {
        $properties = Property::where('tenant_id', tenant_id())->get();
        return view('panels.reservations.form', compact('properties'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'nullable|email',
            'guest_phone' => 'nullable|string|max:20',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'checkin_date' => 'required|date',
            'checkout_date' => 'required|date|after:checkin_date',
            'total_amount' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = tenant_id();
        $validated['code'] = 'RES-' . strtoupper(substr(md5(uniqid()), 0, 8));
        $validated['status'] = 'confirmed';

        $reservation = Reservation::create($validated);
        event(new ReservationCreated($reservation));

        return redirect()->route('reservations.show', $reservation)->with('success', 'Reserva creada');
    }

    public function show(Reservation $reservation)
    {
        $this->authorize('view', $reservation);
        $reservation->load(['property', 'guests', 'checkins', 'sesSubmissions']);
        return view('panels.reservations.show', compact('reservation'));
    }

    public function edit(Reservation $reservation)
    {
        $this->authorize('update', $reservation);
        $properties = Property::where('tenant_id', tenant_id())->get();
        return view('panels.reservations.form', compact('reservation', 'properties'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        $this->authorize('update', $reservation);
        $reservation->update($request->validate([
            'guest_name' => 'sometimes|string|max:255',
            'guest_email' => 'nullable|email',
            'guest_phone' => 'nullable|string|max:20',
            'adults' => 'sometimes|integer|min:1',
            'children' => 'sometimes|integer|min:0',
            'checkin_date' => 'sometimes|date',
            'checkout_date' => 'sometimes|date|after:checkin_date',
            'status' => 'sometimes|string',
            'notes' => 'nullable|string',
        ]));

        return redirect()->route('reservations.show', $reservation)->with('success', 'Reserva actualizada');
    }

    public function destroy(Reservation $reservation)
    {
        $this->authorize('delete', $reservation);
        $reservation->delete();
        return redirect()->route('reservations.index')->with('success', 'Reserva eliminada');
    }
}
