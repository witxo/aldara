<?php

namespace App\Domains\Integration\Models;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    protected $fillable = [
        'tenant_id',
        'provider',
        'event',
        'payload',
        'headers',
        'processed',
        'processed_at',
        'error',
    ];

    protected $casts = [
        'payload' => 'array',
        'headers' => 'array',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
    ];
}
