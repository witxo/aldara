<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domains\Tenant\Models\Tenant;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function tenants(Request $request)
    {
        $query = Tenant::withoutGlobalScopes();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $tenants = $query->withCount(['properties', 'reservations', 'users'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 25);

        return response()->json([
            'data' => $tenants->items(),
            'message' => 'Listado de tenants',
            'status' => 200,
            'meta' => [
                'current_page' => $tenants->currentPage(),
                'per_page' => $tenants->perPage(),
                'total' => $tenants->total(),
                'last_page' => $tenants->lastPage(),
            ],
        ]);
    }

    public function tenantDetail(Tenant $tenant)
    {
        $tenant->loadMissing(['users', 'properties', 'activeSubscription.plan']);
        $tenant->loadCount(['reservations', 'checkins']);

        return response()->json([
            'data' => $tenant,
            'message' => 'Detalle del tenant',
            'status' => 200,
        ]);
    }

    public function stats()
    {
        $totalTenants = Tenant::withoutGlobalScopes()->count();
        $activeTenants = Tenant::withoutGlobalScopes()->whereIn('status', ['active', 'trialing'])->count();
        $totalProperties = \App\Domains\Property\Models\Property::withoutGlobalScopes()->count();
        $totalReservations = \App\Domains\Reservation\Models\Reservation::withoutGlobalScopes()->count();
        $totalUsers = \App\Models\User::count();
        $monthlyRevenue = \App\Domains\Billing\Models\Invoice::withoutGlobalScopes()
            ->where('status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->sum('total');

        return response()->json([
            'data' => [
                'total_tenants' => $totalTenants,
                'active_tenants' => $activeTenants,
                'total_properties' => $totalProperties,
                'total_reservations' => $totalReservations,
                'total_users' => $totalUsers,
                'monthly_revenue' => $monthlyRevenue,
            ],
            'message' => 'Estadísticas de la plataforma',
            'status' => 200,
        ]);
    }

    public function logs(Request $request)
    {
        $logs = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 50);

        return response()->json([
            'data' => $logs->items(),
            'message' => 'Logs de auditoría',
            'status' => 200,
            'meta' => [
                'current_page' => $logs->currentPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
                'last_page' => $logs->lastPage(),
            ],
        ]);
    }
}
