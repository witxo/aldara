<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Domains\Tenant\Models\Tenant;

class SubscriptionActive
{
    protected array $blockedRoutes = [
        'billing.*',
        'integrations.*',
        'ses.*',
        'api.*ses.*',
        'api.*integrations.*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = current_tenant();

        if (!$tenant) {
            return $next($request);
        }

        if (in_array($tenant->status, ['suspended', 'cancelled'])) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'data' => null,
                    'message' => 'Suscripción suspendida. Contacte con soporte.',
                    'status' => 402,
                ], 402);
            }

            return redirect()->route('billing.overdue');
        }

        if ($tenant->status === 'past_due') {
            if ($this->isBlockedRoute($request)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'data' => null,
                        'message' => 'Suscripción vencida. Actualice su método de pago.',
                        'status' => 402,
                    ], 402);
                }

                return redirect()->route('billing.overdue');
            }
        }

        return $next($request);
    }

    protected function isBlockedRoute(Request $request): bool
    {
        $routeName = $request->route()?->getName() ?? '';

        foreach ($this->blockedRoutes as $pattern) {
            if (str_is($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }
}
