<?php

namespace App\Domains\Tenant\Policies;

use App\Models\User;
use App\Domains\Tenant\Models\Tenant;

class TenantPolicy
{
    public function view(User $user, Tenant $tenant): bool
    {
        return $user->is_superadmin || $user->tenants()->where('tenant_id', $tenant->id)->exists();
    }

    public function update(User $user, Tenant $tenant): bool
    {
        if ($user->is_superadmin) return true;
        $role = $user->getRoleForTenant($tenant->id);
        return $role === 'admin';
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return $user->is_superadmin;
    }
}
