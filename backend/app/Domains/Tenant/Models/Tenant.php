<?php

namespace App\Domains\Tenant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;
use App\Traits\Auditable;
use App\Models\User;
use App\Domains\Property\Models\Property;
use App\Domains\Reservation\Models\Reservation;
use App\Domains\Billing\Models\Subscription;

class Tenant extends Model
{
    use SoftDeletes, HasUuid, Auditable;

    protected $fillable = [
        'uuid',
        'company_name',
        'tax_id',
        'email',
        'phone',
        'language',
        'timezone',
        'status',
        'trial_ends_at',
        'settings',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'settings' => 'array',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'tenant_user')
            ->withPivot(['role', 'permissions', 'is_active', 'accepted_at'])
            ->withTimestamps();
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)->whereIn('status', ['active', 'trialing']);
    }

    public function isTrialing(): bool
    {
        return $this->status === 'trialing' && $this->trial_ends_at?->isFuture();
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing']);
    }

    public function isSuspended(): bool
    {
        return in_array($this->status, ['suspended', 'past_due', 'cancelled']);
    }
}
