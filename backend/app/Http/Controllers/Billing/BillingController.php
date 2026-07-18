<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Domains\Tenant\Models\SubscriptionPlan;

class BillingController extends Controller
{
    public function index()
    {
        $tenant = current_tenant();
        $subscription = $tenant->activeSubscription()->with('plan')->first();
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();

        return view('panels.billing.index', compact('subscription', 'plans'));
    }

    public function invoices()
    {
        $invoices = \App\Domains\Billing\Models\Invoice::where('tenant_id', tenant_id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('panels.billing.invoices', compact('invoices'));
    }

    public function changePlan()
    {
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();
        return view('panels.billing.change-plan', compact('plans'));
    }

    public function updatePlan(\Illuminate\Http\Request $request)
    {
        $request->validate(['plan_code' => 'required|exists:subscription_plans,code']);
        $plan = SubscriptionPlan::where('code', $request->plan_code)->firstOrFail();
        $tenant = current_tenant();

        $subscription = $tenant->activeSubscription;
        if ($subscription) {
            $subscription->update(['plan_id' => $plan->id]);
        }

        $tenant->update(['status' => 'active']);

        return redirect()->route('billing.index')->with('success', 'Plan actualizado');
    }
}
