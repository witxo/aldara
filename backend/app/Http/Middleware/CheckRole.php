<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        if ($user->is_superadmin) {
            return $next($request);
        }

        $tenantId = tenant_id();
        $tenantUser = $user->tenants()
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$tenantUser) {
            abort(403, 'No pertenece a este tenant');
        }

        $userRole = $tenantUser->pivot->role;

        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        abort(403, 'Rol insuficiente para esta acción');
    }
}
