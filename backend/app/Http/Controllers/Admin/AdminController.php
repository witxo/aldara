<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Tenant\Models\Tenant;
use App\Domains\Tenant\Models\SubscriptionPlan;
use App\Domains\Tenant\Services\TenantService;
use App\Domains\Billing\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(
        protected TenantService $tenantService,
    ) {}

    public function dashboard()
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::whereIn('status', ['active', 'trialing'])->count();
        $suspendedTenants = Tenant::where('status', 'suspended')->count();
        $pastDueTenants = Tenant::where('status', 'past_due')->count();
        $totalUsers = User::count();
        $recentTenants = Tenant::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalTenants', 'activeTenants', 'suspendedTenants',
            'pastDueTenants', 'totalUsers', 'recentTenants'
        ));
    }

    public function tenants()
    {
        $tenants = Tenant::withCount('users', 'properties')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.tenants.index', compact('tenants'));
    }

    public function createTenant()
    {
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.tenants.create', compact('plans'));
    }

    public function storeTenant(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'tax_id' => 'nullable|string|max:20',
            'plan' => 'required|string|exists:subscription_plans,code',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        $tenant = $this->tenantService->createTenant([
            'company_name' => $validated['company_name'],
            'tax_id' => $validated['tax_id'] ?? null,
            'email' => $validated['email'],
        ], $user, $validated['plan']);

        $tenant->update(['status' => 'active']);

        return redirect()->route('admin.tenants.show', $tenant)
            ->with('success', 'Tenant creado correctamente');
    }

    public function showTenant(Tenant $tenant)
    {
        $tenant->load('users', 'properties', 'subscriptions.plan');
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.tenants.show', compact('tenant', 'plans'));
    }

    public function toggleTenant(Tenant $tenant)
    {
        $tenant->status = $tenant->status === 'suspended' ? 'active' : 'suspended';
        $tenant->save();

        $action = $tenant->status === 'suspended' ? 'suspendido' : 'activado';
        return back()->with('success', "Tenant {$action} correctamente");
    }

    public function changePlan(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'plan_code' => 'required|string|exists:subscription_plans,code',
        ]);

        $activeSub = $tenant->activeSubscription;
        if ($activeSub) {
            $activeSub->update(['status' => 'canceled', 'ends_at' => now()]);
        }

        $this->tenantService->assignPlan($tenant, $validated['plan_code']);

        $tenant->update(['status' => 'active']);

        return back()->with('success', 'Plan cambiado correctamente (pendiente integración Stripe prorrateo)');
    }

    public function users()
    {
        $users = User::with('tenants')->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.users.index', compact('users'));
    }
}
