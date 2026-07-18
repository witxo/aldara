<?php

namespace App\Domains\Integration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;
use App\Domains\Property\Models\Property;
use App\Domains\Reservation\Models\Reservation;

class IcsCalendar extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'property_id',
        'provider',
        'label',
        'url',
        'color',
        'is_active',
        'last_sync_at',
        'last_sync_status',
        'last_error',
        'last_sync_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
        'last_sync_count' => 'integer',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
