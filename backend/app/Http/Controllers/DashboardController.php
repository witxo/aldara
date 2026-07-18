<?php

namespace App\Http\Controllers;

use App\Domains\Reservation\Models\Reservation;
use App\Domains\Checkin\Models\Checkin;
use App\Domains\Guest\Models\Guest;
use App\Domains\Tenant\Models\Tenant;

class DashboardController extends Controller
{
    public function index()
    {
        $tenantId = tenant_id();

        $todayReservations = Reservation::where('tenant_id', $tenantId)
            ->whereDate('checkin_date', now())
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $pendingCheckins = Checkin::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();

        $activeGuests = Guest::where('tenant_id', $tenantId)
            ->whereHas('reservation', function ($q) {
                $q->whereDate('checkout_date', '>=', now())
                  ->whereDate('checkin_date', '<=', now())
                  ->whereNotIn('status', ['cancelled']);
            })->count();

        $upcomingArrivals = Reservation::where('tenant_id', $tenantId)
            ->whereDate('checkin_date', '>', now())
            ->whereDate('checkin_date', '<=', now()->addDays(7))
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $todayReservationsList = Reservation::where('tenant_id', $tenantId)
            ->where(function ($q) {
                $q->whereDate('checkin_date', now())
                  ->orWhereDate('checkout_date', now());
            })
            ->whereNotIn('status', ['cancelled'])
            ->with('property')
            ->orderBy('checkin_date')
            ->get();

        return view('panels.dashboard', compact(
            'todayReservations',
            'pendingCheckins',
            'activeGuests',
            'upcomingArrivals',
            'todayReservationsList'
        ));
    }
}
