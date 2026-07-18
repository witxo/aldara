<?php

namespace App\Domains\Integration\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class PropertyIntegration extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'property_id',
        'tenant_id',
        'provider',
        'provider_id',
        'config',
        'is_connected',
        'last_sync_at',
        'sync_status',
        'metadata',
    ];

    protected $casts = [
        'config' => 'array',
        'is_connected' => 'boolean',
        'last_sync_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function property()
    {
        return $this->belongsTo(\App\Domains\Property\Models\Property::class);
    }
}
