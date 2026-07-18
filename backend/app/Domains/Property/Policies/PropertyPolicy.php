<?php

namespace App\Domains\Property\Policies;

use App\Models\User;
use App\Domains\Property\Models\Property;

class PropertyPolicy
{
    public function view(User $user, Property $property): bool
    {
        return $user->is_superadmin || $user->tenants()->where('tenant_id', $property->tenant_id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->is_superadmin || $user->hasPermission('properties.create');
    }

    public function update(User $user, Property $property): bool
    {
        return $user->is_superadmin || $user->hasPermission('properties.edit', $property->tenant_id);
    }

    public function delete(User $user, Property $property): bool
    {
        return $user->is_superadmin || $user->hasPermission('properties.delete', $property->tenant_id);
    }
}
