<?php

namespace App\Domains\Reservation\Policies;

use App\Models\User;
use App\Domains\Reservation\Models\Reservation;

class ReservationPolicy
{
    public function view(User $user, Reservation $reservation): bool
    {
        return $user->is_superadmin || $user->tenants()->where('tenant_id', $reservation->tenant_id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->is_superadmin || $user->hasPermission('reservations.create');
    }

    public function update(User $user, Reservation $reservation): bool
    {
        return $user->is_superadmin || $user->hasPermission('reservations.edit', $reservation->tenant_id);
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        return $user->is_superadmin || $user->hasPermission('reservations.delete', $reservation->tenant_id);
    }
}
