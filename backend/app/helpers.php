<?php

use App\Domains\Tenant\Models\Tenant;
use Illuminate\Support\Facades\Auth;

if (!function_exists('tenant_id')) {
    function tenant_id(): ?int
    {
        if (session()->has('tenant_id')) {
            return (int) session('tenant_id');
        }

        if (Auth::check() && session()->has('current_tenant_id')) {
            return (int) session('current_tenant_id');
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
    function tenant_ses_config(?string $key = null): mixed
    {
        $tenant = current_tenant();
        $settings = $tenant?->settings ?? [];

        $config = [
            'username' => $settings['ses_username'] ?? config('ses.username'),
            'password' => $settings['ses_password'] ?? config('ses.password'),
            'codigo_arrendador' => $settings['ses_codigo_arrendador'] ?? config('ses.codigo_arrendador'),
            'aplicacion' => config('ses.aplicacion', 'Aldara'),
            'endpoint' => config('ses.endpoint'),
        ];

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
