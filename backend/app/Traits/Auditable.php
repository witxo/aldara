<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            static::logAudit('created', $model, [], $model->toArray());
        });

        static::updated(function ($model) {
            $changed = $model->getDirty();
            $old = [];
            foreach ($changed as $key => $value) {
                $old[$key] = $model->getOriginal($key);
            }
            static::logAudit('updated', $model, $old, $changed);
        });

        static::deleted(function ($model) {
            static::logAudit('deleted', $model, $model->toArray(), []);
        });

        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(static::class))) {
            static::restored(function ($model) {
                static::logAudit('restored', $model, [], $model->toArray());
            });
        }
    }

    protected static function logAudit(string $event, $model, array $old, array $new): void
    {
        if (!config('app.debug') && !app()->runningInConsole()) {
            $sensitiveFields = ['password', 'document_number', 'token', 'remember_token'];
            foreach ($sensitiveFields as $field) {
                if (isset($old[$field])) $old[$field] = '***REDACTED***';
                if (isset($new[$field])) $new[$field] = '***REDACTED***';
            }
        }

        AuditLog::create([
            'tenant_id' => $model->tenant_id ?? tenant_id(),
            'user_id' => Auth::id(),
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'event' => $event,
            'old_values' => !empty($old) ? $old : null,
            'new_values' => !empty($new) ? $new : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
