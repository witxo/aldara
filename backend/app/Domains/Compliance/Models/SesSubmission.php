<?php

namespace App\Domains\Compliance\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class SesSubmission extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'reservation_id',
        'checkin_id',
        'status',
        'mode',
        'payload',
        'response',
        'reference',
        'error_message',
        'retry_count',
        'last_attempt_at',
        'sent_at',
        'acknowledged_at',
        'metadata',
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
        'retry_count' => 'integer',
        'last_attempt_at' => 'datetime',
        'sent_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function reservation()
    {
        return $this->belongsTo(\App\Domains\Reservation\Models\Reservation::class);
    }

    public function checkin()
    {
        return $this->belongsTo(\App\Domains\Checkin\Models\Checkin::class);
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
