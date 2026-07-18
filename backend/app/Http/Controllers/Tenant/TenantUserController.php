<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Domains\Tenant\Models\Tenant;
use Illuminate\Http\Request;

class TenantUserController extends Controller
{
    public function index()
    {
        $tenant = current_tenant();
        $users = $tenant->users()->withPivot('role', 'is_active', 'accepted_at')->get();

        return view('panels.users.index', compact('users'));
    }

    public function create()
    {
        return view('panels.users.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:admin,operator',
        ]);

        $tenant = current_tenant();
        $user = User::where('email', $validated['email'])->firstOrFail();

        if ($tenant->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'El usuario ya pertenece a este tenant');
        }

        $tenant->users()->attach($user->id, [
            'role' => $validated['role'],
            'is_active' => true,
            'invited_at' => now(),
        ]);

        return redirect()->route('tenant-users.index')->with('success', 'Usuario añadido');
    }

    public function edit(User $user)
    {
        $tenant = current_tenant();
        $tenantUser = $tenant->users()->where('user_id', $user->id)->firstOrFail();

        return view('panels.users.form', compact('user', 'tenantUser'));
    }

    public function update(Request $request, User $user)
    {
        $tenant = current_tenant();
        $tenant->users()->updateExistingPivot($user->id, [
            'role' => $request->validate(['role' => 'required|in:admin,operator'])['role'],
        ]);

        return redirect()->route('tenant-users.index')->with('success', 'Rol actualizado');
    }

    public function destroy(User $user)
    {
        $tenant = current_tenant();
        $tenant->users()->detach($user->id);

        return redirect()->route('tenant-users.index')->with('success', 'Usuario eliminado');
    }
}
