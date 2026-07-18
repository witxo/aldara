<?php

namespace App\Domains\Reservation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Traits\Auditable;
use App\Domains\Guest\Models\Guest;
use App\Domains\Checkin\Models\Checkin;
use App\Domains\Compliance\Models\SesSubmission;

class Reservation extends Model
{
    use SoftDeletes, BelongsToTenant, HasUuid, Auditable;

    protected $fillable = [
        'tenant_id',
        'property_id',
        'uuid',
        'code',
        'external_code',
        'source',
        'status',
        'guest_name',
        'guest_email',
        'guest_phone',
        'adults',
        'children',
        'infants',
        'checkin_date',
        'checkout_date',
        'checkin_time',
        'checkout_time',
        'total_amount',
        'currency',
        'notes',
        'channel_data',
        'checked_in_at',
        'checked_out_at',
        'checkin_token',
        'checkin_token_expires_at',
        'ics_calendar_id',
    ];

    protected $casts = [
        'checkin_date' => 'date',
        'checkout_date' => 'date',
        'checkin_time' => 'datetime:H:i',
        'checkout_time' => 'datetime:H:i',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'checkin_token_expires_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'channel_data' => 'array',
    ];

    public function property()
    {
        return $this->belongsTo(\App\Domains\Property\Models\Property::class);
    }

    public function icsCalendar()
    {
        return $this->belongsTo(\App\Domains\Integration\Models\IcsCalendar::class);
    }

    public function guests()
    {
        return $this->hasMany(Guest::class);
    }

    public function checkins()
    {
        return $this->hasMany(Checkin::class);
    }

    public function sesSubmissions()
    {
        return $this->hasMany(SesSubmission::class);
    }

    public function mainGuest()
    {
        return $this->hasOne(Guest::class)->where('is_main_guest', true);
    }

    public function getTotalGuestsAttribute(): int
    {
        return $this->adults + $this->children;
    }

    public function generateCheckinToken(): string
    {
        $token = \Illuminate\Support\Str::random(40);
        $this->update([
            'checkin_token' => $token,
            'checkin_token_expires_at' => now()->addHours(config('checkin.token_expiry_hours', 48)),
            'status' => 'checkin_sent',
        ]);
        return $token;
    }

    public function isTokenValid(): bool
    {
        return $this->checkin_token
            && $this->checkin_token_expires_at
            && $this->checkin_token_expires_at->isFuture()
            && !in_array($this->status, ['completed', 'cancelled']);
    }

    public function scopeForDateRange($query, $from, $to)
    {
        return $query->where(function ($q) use ($from, $to) {
            $q->whereBetween('checkin_date', [$from, $to])
              ->orWhereBetween('checkout_date', [$from, $to])
              ->orWhere(function ($qq) use ($from, $to) {
                  $qq->where('checkin_date', '<=', $from)
                     ->where('checkout_date', '>=', $to);
              });
        });
    }
}
