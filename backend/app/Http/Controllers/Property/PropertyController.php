<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Domains\Property\Models\Property;
use App\Domains\Compliance\Services\SesService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = Property::where('tenant_id', tenant_id())->paginate(15);
        return view('panels.properties.index', compact('properties'));
    }

    public function create()
    {
        return view('panels.properties.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'address_line1' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'license_number' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:1',
            'checkin_time' => 'nullable|date_format:H:i',
            'checkout_time' => 'nullable|date_format:H:i',
            'ses_establecimiento_code' => 'nullable|string|max:10',
            'ses_username' => 'nullable|string|max:255',
            'ses_password' => 'nullable|string|max:255',
            'ses_codigo_arrendador' => 'nullable|string|max:10',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $validated['tenant_id'] = tenant_id();
        $validated['logo'] = $request->file('logo')?->store('properties', 'public');
        $property = Property::create($validated);

        return redirect()->route('properties.index')->with('success', 'Alojamiento creado');
    }

    public function show(Property $property)
    {
        $this->authorize('view', $property);
        return view('panels.properties.show', compact('property'));
    }

    public function edit(Property $property)
    {
        $this->authorize('update', $property);
        return view('panels.properties.form', compact('property'));
    }

    public function update(Request $request, Property $property)
    {
        $this->authorize('update', $property);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string',
            'address_line1' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:10',
            'license_number' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
            'checkin_time' => 'nullable|date_format:H:i',
            'checkout_time' => 'nullable|date_format:H:i',
            'ses_establecimiento_code' => 'nullable|string|max:10',
            'ses_username' => 'nullable|string|max:255',
            'ses_password' => 'nullable|string|max:255',
            'ses_codigo_arrendador' => 'nullable|string|max:10',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_logo' => 'nullable|boolean',
        ]);

        if (empty($validated['ses_password'])) {
            unset($validated['ses_password']);
        }

        if ($request->boolean('remove_logo') && $property->logo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($property->logo);
            $validated['logo'] = null;
        } elseif ($request->hasFile('logo')) {
            if ($property->logo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($property->logo);
            }
            $validated['logo'] = $request->file('logo')->store('properties', 'public');
        } else {
            unset($validated['logo']);
        }

        unset($validated['remove_logo']);

        $property->update($validated);

        return redirect()->route('properties.index')->with('success', 'Alojamiento actualizado');
    }

    public function destroy(Property $property)
    {
        $this->authorize('delete', $property);
        $property->delete();
        return redirect()->route('properties.index')->with('success', 'Alojamiento eliminado');
    }

    public function testSes(Property $property, SesService $sesService)
    {
        $this->authorize('view', $property);

        $result = $sesService->ping($property);

        if (!$result['success']) {
            $error = $result['descripcion'] ?? '';
            if (!empty($result['codigo'])) {
                $error = "Código {$result['codigo']}: {$error}";
            }
            $soapPreview = !empty($result['soap_request'])
                ? ' | Petición: ' . htmlspecialchars(substr($result['soap_request'], 0, 800))
                : '';
            $rawPreview = !empty($result['raw'])
                ? ' | Respuesta: ' . htmlspecialchars(substr($result['raw'], 0, 800))
                : '';
            return redirect()->route('properties.show', $property)->with('error', 'Error SES: ' . ($error ?: 'Error de conexión') . $soapPreview . $rawPreview);
        }

        return redirect()->route('properties.show', $property)->with('success', 'Conexión SES exitosa. Código: ' . $result['codigo']);
    }

    public function logo(Property $property)
    {
        if (!$property->logo) {
            abort(404);
        }

        $path = storage_path('app/public/' . $property->logo);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
