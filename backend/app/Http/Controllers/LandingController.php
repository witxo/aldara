<?php

namespace App\Http\Controllers;

use App\Domains\Tenant\Models\SubscriptionPlan;

class LandingController extends Controller
{
    public function show()
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('landing', compact('plans'));
    }
}
