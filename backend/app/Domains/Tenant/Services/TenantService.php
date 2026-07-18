<?php

namespace App\Domains\Tenant\Services;

use App\Domains\Tenant\Models\Tenant;
use App\Domains\Tenant\Models\SubscriptionPlan;
use App\Domains\Billing\Models\Subscription;
use App\Models\User;

class TenantService
{
    public function createTenant(array $data, User $user, string $planCode = 'basico'): Tenant
    {
        $tenant = Tenant::create([
            'company_name' => $data['company_name'],
            'tax_id' => $data['tax_id'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'language' => $data['language'] ?? 'es',
            'timezone' => $data['timezone'] ?? 'Europe/Madrid',
            'status' => 'trialing',
            'trial_ends_at' => now()->addDays(15),
        ]);

        $tenant->users()->attach($user->id, [
            'role' => 'admin',
            'is_active' => true,
            'accepted_at' => now(),
        ]);

        $this->assignPlan($tenant, $planCode);

        return $tenant;
    }

    public function assignPlan(Tenant $tenant, string $planCode): Subscription
    {
        $plan = SubscriptionPlan::where('code', $planCode)->firstOrFail();

        return Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'trialing',
            'trial_ends_at' => $tenant->trial_ends_at,
            'starts_at' => now(),
        ]);
    }

    public function canAddProperty(Tenant $tenant): bool
    {
        $subscription = $tenant->activeSubscription;
        if (!$subscription) return false;

        $plan = $subscription->plan;
        $currentCount = $tenant->properties()->count();

        return $currentCount < $plan->max_properties;
    }

    public function canAddUser(Tenant $tenant): bool
    {
        $subscription = $tenant->activeSubscription;
        if (!$subscription) return false;

        $plan = $subscription->plan;
        $currentCount = $tenant->users()->count();

        return $currentCount < $plan->max_users;
    }

    public function canCreateReservation(Tenant $tenant): bool
    {
        $subscription = $tenant->activeSubscription;
        if (!$subscription) return false;

        $plan = $subscription->plan;
        if ($plan->max_reservations < 0) return true;

        $monthlyCount = $tenant->reservations()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return $monthlyCount < $plan->max_reservations;
    }
}
