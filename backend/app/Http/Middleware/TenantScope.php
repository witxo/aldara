<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantScope
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!tenant_id()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'data' => null,
                    'message' => 'Se requiere un tenant activo',
                    'status' => 400,
                ], 400);
            }

            return redirect()->route('tenant.select');
        }

        return $next($request);
    }
}
