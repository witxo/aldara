<?php

namespace App\Domains\Guest\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Traits\Auditable;

class Guest extends Model
{
    use SoftDeletes, BelongsToTenant, HasUuid, Auditable;

    protected $fillable = [
        'tenant_id',
        'reservation_id',
        'checkin_id',
        'uuid',
        'first_name',
        'last_name',
        'document_type',
        'document_number',
        'nationality',
        'birth_date',
        'gender',
        'is_main_guest',
        'parentesco',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'address_line1',
        'address_line2',
        'address_city',
        'address_postal_code',
        'address_country',
        'metadata',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_main_guest' => 'boolean',
        'metadata' => 'array',
        'document_number' => 'encrypted',
    ];

    public function reservation()
    {
        return $this->belongsTo(\App\Domains\Reservation\Models\Reservation::class);
    }

    public function checkin()
    {
        return $this->belongsTo(\App\Domains\Checkin\Models\Checkin::class);
    }

    public function documents()
    {
        return $this->hasMany(GuestDocument::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
