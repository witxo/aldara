<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domains\Integration\Models\PropertyIntegration;
use App\Domains\Integration\Services\IntegrationService;
use App\Domains\Property\Models\Property;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    public function __construct(
        protected IntegrationService $integrationService,
    ) {}

    public function index()
    {
        $integrations = PropertyIntegration::where('tenant_id', tenant_id())
            ->with('property')
            ->get();

        return response()->json([
            'data' => $integrations,
            'message' => 'Listado de integraciones',
            'status' => 200,
        ]);
    }

    public function connect(Request $request, Property $property)
    {
        $this->authorize('update', $property);

        $validated = $request->validate([
            'provider' => 'required|in:booking,airbnb,ics,pms',
            'provider_id' => 'nullable|string|max:255',
            'config' => 'nullable|array',
        ]);

        $integration = PropertyIntegration::updateOrCreate(
            [
                'property_id' => $property->id,
                'provider' => $validated['provider'],
            ],
            [
                'tenant_id' => tenant_id(),
                'provider_id' => $validated['provider_id'] ?? null,
                'config' => $validated['config'] ?? [],
                'is_connected' => false,
            ]
        );

        $connector = $this->integrationService->getConnector($validated['provider']);

        if ($connector) {
            $result = $connector->connect($integration);
            $integration->update([
                'is_connected' => $result['success'] ?? false,
                'sync_status' => $result['success'] ? 'connected' : 'error',
            ]);
        }

        return response()->json([
            'data' => $integration->fresh(),
            'message' => 'Integración configurada',
            'status' => 200,
        ]);
    }

    public function importIcs(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'file' => 'required|file|mimes:ics,txt|max:5120',
        ]);

        $property = Property::findOrFail($request->property_id);
        $this->authorize('update', $property);

        $content = file_get_contents($request->file('file')->getRealPath());
        $result = $this->integrationService->importIcs($content, $property);

        return response()->json([
            'data' => $result,
            'message' => "Importación ICS completada: {$result['imported']} reservas importadas",
            'status' => 200,
        ]);
    }
}
