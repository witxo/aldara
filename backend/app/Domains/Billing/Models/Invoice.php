<?php

namespace App\Domains\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Invoice extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'stripe_invoice_id',
        'number',
        'status',
        'currency',
        'total',
        'tax_percentage',
        'tax_amount',
        'paid_at',
        'pdf_url',
        'lines',
        'metadata',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'lines' => 'array',
        'metadata' => 'array',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
