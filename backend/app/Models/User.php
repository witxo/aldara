<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HasUuid;
use App\Domains\Tenant\Models\Tenant;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasUuid;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'phone',
        'password',
        'language',
        'is_superadmin',
        'email_verified_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_superadmin' => 'boolean',
        'two_factor_confirmed_at' => 'datetime',
    ];

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')
            ->withPivot(['role', 'permissions', 'is_active', 'accepted_at'])
            ->withTimestamps();
    }

    public function activeTenants()
    {
        return $this->tenants()->wherePivot('is_active', true);
    }

    public function getRoleForTenant(int $tenantId): ?string
    {
        $tenantUser = $this->tenants()->where('tenant_id', $tenantId)->first();
        return $tenantUser?->pivot->role;
    }

    public function hasPermission(string $permission, ?int $tenantId = null): bool
    {
        if ($this->is_superadmin) {
            return true;
        }

        $tenantId = $tenantId ?? tenant_id();
        $tenantUser = $this->tenants()->where('tenant_id', $tenantId)->first();

        if (!$tenantUser) {
            return false;
        }

        $role = $tenantUser->pivot->role;

        $rolePermissions = [
            'admin' => ['*'],
            'operator' => ['reservations.view', 'guests.view', 'checkins.create', 'checkins.view', 'properties.view'],
        ];

        if ($role === 'admin') {
            return true;
        }

        $permissions = $rolePermissions[$role] ?? [];

        foreach ($permissions as $perm) {
            if (str_is($perm, $permission)) {
                return true;
            }
        }

        $extraPermissions = $tenantUser->pivot->permissions ?? [];
        foreach ($extraPermissions as $perm) {
            if (str_is($perm, $permission)) {
                return true;
            }
        }

        return false;
    }
}
