<?php

namespace App\Domains\Property\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Traits\Auditable;
use Illuminate\Support\Str;

class Property extends Model
{
    use SoftDeletes, BelongsToTenant, HasUuid, Auditable;

    protected $fillable = [
        'tenant_id',
        'uuid',
        'checkin_code',
        'name',
        'type',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'license_number',
        'logo',
        'ses_establecimiento_code',
        'ses_username',
        'ses_password',
        'ses_codigo_arrendador',
        'capacity',
        'checkin_time',
        'checkout_time',
        'currency',
        'language',
        'timezone',
        'is_active',
        'settings',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'checkin_time' => 'datetime:H:i',
        'checkout_time' => 'datetime:H:i',
        'settings' => 'array',
        'metadata' => 'array',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo || !\Illuminate\Support\Facades\Route::has('property.logo')) {
            return null;
        }
        return route('property.logo', $this);
    }

    public function reservations()
    {
        return $this->hasMany(\App\Domains\Reservation\Models\Reservation::class);
    }

    public function integrations()
    {
        return $this->hasMany(\App\Domains\Integration\Models\PropertyIntegration::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($property) {
            if (empty($property->checkin_code)) {
                $property->checkin_code = Str::random(32);
            }
        });
    }
}
