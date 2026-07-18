<?php

namespace App\Domains\Guest\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;

class GuestDocument extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'guest_id',
        'checkin_id',
        'type',
        'filename',
        'original_name',
        'mime_type',
        'size',
        'disk',
        'path',
        'metadata',
    ];

    protected $casts = [
        'size' => 'integer',
        'metadata' => 'array',
    ];

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function getUrlAttribute(): string
    {
        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'guest-documents.download',
            now()->addMinutes(60),
            ['id' => $this->id]
        );
    }
}
