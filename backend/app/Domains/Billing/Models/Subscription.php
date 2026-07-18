<?php

namespace App\Domains\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Subscription extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'stripe_id',
        'stripe_price_id',
        'status',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'next_payment_at',
        'canceled_at',
        'metadata',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'next_payment_at' => 'datetime',
        'canceled_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function plan()
    {
        return $this->belongsTo(\App\Domains\Tenant\Models\SubscriptionPlan::class, 'plan_id');
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Domains\Tenant\Models\Tenant::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing']);
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trialing'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }
}
