<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
            'use_web_mrz_reader' => 'boolean',
            
        ]);

        $settings = $tenant->settings ?? [];

        foreach ($validated as $key => $value) {
            $settings[$key] = $value;
        }

        $tenant->update(['settings' => $settings]);

        cache()->forget('tenant.' . $tenant->id);

        return redirect()->route('settings.index')->with('success', 'Ajustes actualizados');
    }
}
