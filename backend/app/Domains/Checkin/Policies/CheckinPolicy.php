<?php

namespace App\Domains\Checkin\Policies;

use App\Models\User;
use App\Domains\Checkin\Models\Checkin;

class CheckinPolicy
{
    public function view(User $user, Checkin $checkin): bool
    {
        return $user->is_superadmin || $user->tenants()->where('tenant_id', $checkin->tenant_id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->is_superadmin || $user->hasPermission('checkins.create');
    }

    public function update(User $user, Checkin $checkin): bool
    {
        return $user->is_superadmin || $user->hasPermission('checkins.update', $checkin->tenant_id);
    }

    public function verify(User $user, Checkin $checkin): bool
    {
        if ($user->is_superadmin) return true;
        $role = $user->getRoleForTenant($checkin->tenant_id);
        return $role === 'admin';
    }
}
