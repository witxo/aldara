<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = null;

        if ($request->user() && $request->is('api/*')) {
            $tenantId = session()->get('current_tenant_id');
        }

        if ($request->user() && !$request->is('api/*')) {
            $tenantId = session()->get('current_tenant_id');
        }

        if ($request->hasHeader('X-Tenant-Id')) {
            $requestedTenant = (int) $request->header('X-Tenant-Id');
            if ($request->user() && in_array($requestedTenant, user_tenant_ids())) {
                $tenantId = $requestedTenant;
                session()->put('current_tenant_id', $tenantId);
            }
        }

        if ($tenantId) {
            session()->put('current_tenant_id', $tenantId);
            session()->put('tenant_id', $tenantId);
        }

        return $next($request);
    }
}
