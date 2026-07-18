<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domains\Tenant\Models\Tenant;
use App\Domains\Tenant\Services\TenantService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantController extends Controller
{
    public function __construct(
        protected TenantService $tenantService,
    ) {}

    public function index()
    {
        $tenants = Auth::user()->activeTenants;

        return response()->json([
            'data' => $tenants->map(fn($t) => [
                'id' => $t->id,
                'uuid' => $t->uuid,
                'company_name' => $t->company_name,
                'tax_id' => $t->tax_id,
                'email' => $t->email,
                'phone' => $t->phone,
                'status' => $t->status,
                'role' => $t->pivot->role,
            ]),
            'message' => 'Listado de tenants',
            'status' => 200,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'tax_id' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $tenant = $this->tenantService->createTenant($validated, Auth::user());

        return response()->json([
            'data' => [
                'id' => $tenant->id,
                'uuid' => $tenant->uuid,
                'company_name' => $tenant->company_name,
                'status' => $tenant->status,
            ],
            'message' => 'Tenant creado correctamente',
            'status' => 201,
        ], 201);
    }

    public function show(Tenant $tenant)
    {
        $this->authorize('view', $tenant);

        return response()->json([
            'data' => [
                'id' => $tenant->id,
                'uuid' => $tenant->uuid,
                'company_name' => $tenant->company_name,
                'tax_id' => $tenant->tax_id,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'language' => $tenant->language,
                'timezone' => $tenant->timezone,
                'status' => $tenant->status,
                'trial_ends_at' => $tenant->trial_ends_at,
                'created_at' => $tenant->created_at,
            ],
            'message' => 'Detalle del tenant',
            'status' => 200,
        ]);
    }

    public function update(Request $request, Tenant $tenant)
    {
        $this->authorize('update', $tenant);

        $validated = $request->validate([
            'company_name' => 'sometimes|string|max:255',
            'tax_id' => 'nullable|string|max:20',
            'email' => 'sometimes|email|max:255',
            'phone' => 'nullable|string|max:20',
            'language' => 'sometimes|string|in:es,en',
            'timezone' => 'sometimes|string|max:50',
        ]);

        $tenant->update($validated);

        return response()->json([
            'data' => $tenant,
            'message' => 'Tenant actualizado',
            'status' => 200,
        ]);
    }

    public function users(Tenant $tenant)
    {
        $this->authorize('view', $tenant);

        return response()->json([
            'data' => $tenant->users->map(fn($u) => [
                'id' => $u->id,
                'uuid' => $u->uuid,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->pivot->role,
                'is_active' => $u->pivot->is_active,
                'accepted_at' => $u->pivot->accepted_at,
            ]),
            'message' => 'Usuarios del tenant',
            'status' => 200,
        ]);
    }

    public function inviteUser(Request $request, Tenant $tenant)
    {
        $this->authorize('update', $tenant);

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|string|in:admin,operator',
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();

        if ($tenant->users()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'data' => null,
                'message' => 'El usuario ya pertenece a este tenant',
                'status' => 409,
            ], 409);
        }

        $tenant->users()->attach($user->id, [
            'role' => $validated['role'],
            'is_active' => true,
            'invited_at' => now(),
        ]);

        return response()->json([
            'data' => null,
            'message' => 'Usuario invitado correctamente',
            'status' => 200,
        ]);
    }

    public function removeUser(Tenant $tenant, User $user)
    {
        $this->authorize('update', $tenant);

        $tenant->users()->detach($user->id);

        return response()->json([
            'data' => null,
            'message' => 'Usuario eliminado del tenant',
            'status' => 200,
        ]);
    }
}
