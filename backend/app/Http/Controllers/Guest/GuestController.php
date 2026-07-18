<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Domains\Guest\Models\Guest;

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

    public function show(Guest $guest)
    {
        $this->authorize('view', $guest);
        $guest->load(['reservation.property', 'documents']);
        return view('panels.guests.show', compact('guest'));
    }
}
