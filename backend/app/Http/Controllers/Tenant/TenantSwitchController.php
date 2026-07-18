<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Domains\Tenant\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantSwitchController extends Controller
{
    public function showSelector()
    {
        $tenants = Auth::user()->activeTenants;
        return view('panels.tenant-selector', compact('tenants'));
    }

    public function switch(Request $request)
    {
        $request->validate(['tenant_id' => 'required|exists:tenants,id']);

        $tenant = Auth::user()->activeTenants()
            ->where('tenant_id', $request->tenant_id)
            ->first();

        if (!$tenant) {
            return redirect()->back()->with('error', 'Tenant no encontrado');
        }

        session()->put('current_tenant_id', $tenant->id);

        return redirect()->route('dashboard');
    }
}
