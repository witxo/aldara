<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domains\Compliance\Services\SesService;

class SettingsController extends Controller
{
    public function index()
    {
        $tenant = current_tenant();
        $settings = $tenant?->settings ?? [];
        return view('panels.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $tenant = current_tenant();
        $validated = $request->validate([
            'checkin_require_signature' => 'boolean',
            'checkin_require_document' => 'boolean',
            'default_checkin_time' => 'date_format:H:i',
            'default_checkout_time' => 'date_format:H:i',
            'retention_days' => 'integer|min:30|max:3650',
        ]);

        $settings = $tenant->settings ?? [];

        foreach ($validated as $key => $value) {
            if ($request->has($key)) {
                $settings[$key] = $value;
            }
        }

        $tenant->update(['settings' => $settings]);

        return redirect()->route('settings.index')->with('success', 'Ajustes actualizados');
    }

    public function testSes(SesService $sesService)
    {
        $result = $sesService->ping();

        if (!$result['success']) {
            return redirect()->route('settings.index')->with('error', 'Error SES: ' . ($result['descripcion'] ?? 'Error de conexión'));
        }

        return redirect()->route('settings.index')->with('success', 'Conexión SES exitosa. Código: ' . $result['codigo']);
    }
}
