<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->is_superadmin) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'data' => null,
                    'message' => 'Acceso restringido a administradores',
                    'status' => 403,
                ], 403);
            }

            abort(403, 'Acceso restringido a administradores');
        }

        return $next($request);
    }
}
