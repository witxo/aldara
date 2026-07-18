<?php

namespace App\Domains\Guest\Policies;

use App\Models\User;
use App\Domains\Guest\Models\Guest;

class GuestPolicy
{
    public function view(User $user, Guest $guest): bool
    {
        return $user->is_superadmin || $user->tenants()->where('tenant_id', $guest->tenant_id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->is_superadmin || $user->hasPermission('guests.create');
    }

    public function update(User $user, Guest $guest): bool
    {
        return $user->is_superadmin || $user->hasPermission('guests.edit', $guest->tenant_id);
    }

    public function delete(User $user, Guest $guest): bool
    {
        return $user->is_superadmin || $user->hasPermission('guests.delete', $guest->tenant_id);
    }
}
