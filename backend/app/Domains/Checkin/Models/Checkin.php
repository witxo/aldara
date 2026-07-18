<?php

namespace App\Domains\Checkin\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Checkin extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'reservation_id',
        'type',
        'status',
        'completed_at',
        'verified_at',
        'verified_by',
        'signature_data',
        'consent_legal',
        'consent_marketing',
        'consent_data_retention',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'verified_at' => 'datetime',
        'consent_legal' => 'boolean',
        'consent_marketing' => 'boolean',
        'consent_data_retention' => 'boolean',
        'metadata' => 'array',
    ];

    public function reservation()
    {
        return $this->belongsTo(\App\Domains\Reservation\Models\Reservation::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'verified_by');
    }

    public function guestDocuments()
    {
        return $this->hasMany(\App\Domains\Guest\Models\GuestDocument::class);
    }

    public function sesSubmissions()
    {
        return $this->hasMany(\App\Domains\Compliance\Models\SesSubmission::class);
    }
}
