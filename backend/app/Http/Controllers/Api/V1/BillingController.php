<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domains\Billing\Models\Subscription;
use App\Domains\Tenant\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function subscription()
    {
        $tenant = current_tenant();
        $subscription = $tenant->activeSubscription()->with('plan')->first();

        if (!$subscription) {
            return response()->json([
                'data' => null,
                'message' => 'Sin suscripción activa',
                'status' => 404,
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $subscription->id,
                'status' => $subscription->status,
                'plan' => $subscription->plan,
                'trial_ends_at' => $subscription->trial_ends_at,
                'next_payment_at' => $subscription->next_payment_at,
                'canceled_at' => $subscription->canceled_at,
                'current_period_start' => $subscription->starts_at,
                'current_period_end' => $subscription->ends_at,
            ],
            'message' => 'Suscripción actual',
            'status' => 200,
        ]);
    }

    public function changePlan(Request $request)
    {
        $validated = $request->validate([
            'plan_code' => 'required|string|exists:subscription_plans,code',
        ]);

        $plan = SubscriptionPlan::where('code', $validated['plan_code'])->firstOrFail();
        $tenant = current_tenant();

        $subscription = $tenant->activeSubscription;

        if ($subscription && $subscription->plan_id === $plan->id) {
            return response()->json([
                'data' => null,
                'message' => 'Ya está en este plan',
                'status' => 409,
            ], 409);
        }

        if ($subscription) {
            $subscription->update([
                'plan_id' => $plan->id,
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'changed_from' => $subscription->plan_id,
                    'changed_at' => now()->toIso8601String(),
                ]),
            ]);
        } else {
            Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
            ]);
        }

        $tenant->update(['status' => 'active']);

        return response()->json([
            'data' => ['plan' => $plan],
            'message' => 'Plan actualizado correctamente',
            'status' => 200,
        ]);
    }

    public function invoices()
    {
        $invoices = \App\Domains\Billing\Models\Invoice::where('tenant_id', tenant_id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'data' => $invoices->items(),
            'message' => 'Historial de facturas',
            'status' => 200,
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
                'last_page' => $invoices->lastPage(),
            ],
        ]);
    }
}
