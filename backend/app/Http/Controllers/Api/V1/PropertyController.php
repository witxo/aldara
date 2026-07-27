<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domains\Property\Models\Property;
use App\Domains\Reservation\Models\Reservation;
use App\Domains\Tenant\Services\TenantService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $properties = Property::where('tenant_id', tenant_id())
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->get();

        return response()->json([
            'data' => $properties,
            'message' => 'Listado de alojamientos',
            'status' => 200,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:apartment,house,villa,studio,hotel,rural,other',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'country' => 'required|string|size:2',
            'license_number' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:1',
            'checkin_time' => 'nullable|date_format:H:i',
            'checkout_time' => 'nullable|date_format:H:i',
            'ses_establecimiento_code' => 'nullable|string|max:10',
            'ses_username' => 'nullable|string|max:255',
            'ses_password' => 'nullable|string|max:255',
            'ses_codigo_arrendador' => 'nullable|string|max:10',
        ]);

        $tenant = current_tenant();
        $service = app(TenantService::class);
        if (!$service->canAddProperty($tenant)) {
            $plan = $tenant->activeSubscription?->plan;
            $limit = $plan ? $plan->max_properties : 0;
            return response()->json([
                'data' => null,
                'message' => "Has alcanzado el límite de {$limit} alojamiento(s) de tu plan.",
                'status' => 403,
            ], 403);
        }

        $validated['tenant_id'] = $tenant->id;
        $property = Property::create($validated);

        return response()->json([
            'data' => $property,
            'message' => 'Alojamiento creado',
            'status' => 201,
        ], 201);
    }

    public function show(Property $property)
    {
        $this->authorize('view', $property);

        return response()->json([
            'data' => $property,
            'message' => 'Detalle del alojamiento',
            'status' => 200,
        ]);
    }

    public function update(Request $request, Property $property)
    {
        $this->authorize('update', $property);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:apartment,house,villa,studio,hotel,rural,other',
            'address_line1' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:10',
            'license_number' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:1',
            'is_active' => 'sometimes|boolean',
            'checkin_time' => 'nullable|date_format:H:i',
            'checkout_time' => 'nullable|date_format:H:i',
            'ses_establecimiento_code' => 'nullable|string|max:10',
            'ses_username' => 'nullable|string|max:255',
            'ses_password' => 'nullable|string|max:255',
            'ses_codigo_arrendador' => 'nullable|string|max:10',
        ]);

        if (empty($validated['ses_password'])) {
            unset($validated['ses_password']);
        }

        $property->update($validated);

        return response()->json([
            'data' => $property,
            'message' => 'Alojamiento actualizado',
            'status' => 200,
        ]);
    }

    public function destroy(Property $property)
    {
        $this->authorize('delete', $property);
        $property->delete();

        return response()->json([
            'data' => null,
            'message' => 'Alojamiento eliminado',
            'status' => 200,
        ]);
    }

    public function reservations(Property $property)
    {
        $this->authorize('view', $property);

        $reservations = $property->reservations()
            ->orderBy('checkin_date', 'desc')
            ->paginate(15);

        return response()->json([
            'data' => $reservations->items(),
            'message' => 'Reservas del alojamiento',
            'status' => 200,
            'meta' => [
                'current_page' => $reservations->currentPage(),
                'per_page' => $reservations->perPage(),
                'total' => $reservations->total(),
                'last_page' => $reservations->lastPage(),
            ],
        ]);
    }
}
