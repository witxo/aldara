<?php

use App\Domains\Property\Models\Property;
use App\Domains\Tenant\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

if (!function_exists('tenant_id')) {
    function tenant_id(): ?int
    {
        if (session()->has('tenant_id')) {
            return (int) session('tenant_id');
        }

        if (Auth::check() && session()->has('current_tenant_id')) {
            return (int) session('current_tenant_id');
        }

        if ($header = Request::header('X-Tenant-Id')) {
            return (int) $header;
        }

        if (Auth::check()) {
            $tenants = Auth::user()->tenants()->pluck('tenants.id');
            if ($tenants->count() === 1) {
                return (int) $tenants->first();
            }
        }

        return null;
    }
}

if (!function_exists('current_tenant')) {
    function current_tenant(): ?Tenant
    {
        $id = tenant_id();
        if (!$id) {
            return null;
        }

        return cache()->remember("tenant.{$id}", 3600, function () use ($id) {
            return Tenant::withoutGlobalScopes()->find($id);
        });
    }
}

if (!function_exists('tenant_ses_config')) {
    function tenant_ses_config(?string $key = null, ?Property $property = null): mixed
    {
        $tenant = current_tenant();
        $settings = $tenant?->settings ?? [];

        if ($property) {
            $config = [
                'username' => $property->ses_username ?? $settings['ses_username'] ?? config('ses.username'),
                'password' => $property->ses_password ?? $settings['ses_password'] ?? config('ses.password'),
                'codigo_arrendador' => $property->ses_codigo_arrendador ?? $settings['ses_codigo_arrendador'] ?? config('ses.codigo_arrendador'),
                'aplicacion' => config('ses.aplicacion', 'HospedaCheck'),
                'endpoint' => config('ses.endpoint'),
            ];
        } else {
            $config = [
                'username' => $settings['ses_username'] ?? config('ses.username'),
                'password' => $settings['ses_password'] ?? config('ses.password'),
                'codigo_arrendador' => $settings['ses_codigo_arrendador'] ?? config('ses.codigo_arrendador'),
                'aplicacion' => config('ses.aplicacion', 'HospedaCheck'),
                'endpoint' => config('ses.endpoint'),
            ];
        }

        if ($key) {
            return $config[$key] ?? null;
        }

        return $config;
    }
}

if (!function_exists('user_tenant_ids')) {
    function user_tenant_ids(): array
    {
        return Auth::user()?->tenants()->pluck('tenants.id')->toArray() ?? [];
    }
}
