<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Domains\Tenant\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function __construct(
        protected TenantService $tenantService,
    ) {}

    public function showRegistrationForm()
    {
        $plans = \App\Domains\Tenant\Models\SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();
        return view('auth.register', compact('plans'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'company_name' => 'required|string|max:255',
            'tax_id' => 'nullable|string|max:20',
            'plan' => 'required|string|in:basic,advanced',
            'recaptcha_token' => 'required|recaptcha_v3:register',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $tenant = $this->tenantService->createTenant([
            'company_name' => $validated['company_name'],
            'tax_id' => $validated['tax_id'] ?? null,
            'email' => $validated['email'],
        ], $user, $validated['plan']);

        Auth::login($user);

        session()->put('current_tenant_id', $tenant->id);

        return redirect()->route('dashboard');
    }
}
