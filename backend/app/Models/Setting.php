<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'key',
        'value',
        'type',
    ];

    public static function getValue(string $key, $default = null, ?int $tenantId = null, ?int $userId = null)
    {
        $query = static::where('key', $key);
        if ($tenantId) $query->where('tenant_id', $tenantId);
        if ($userId) $query->where('user_id', $userId);

        $setting = $query->first();

        if (!$setting) return $default;

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }
}
