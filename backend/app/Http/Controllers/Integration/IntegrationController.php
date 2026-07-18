<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Domains\Integration\Models\PropertyIntegration;
use App\Domains\Integration\Models\IcsCalendar;
use App\Domains\Integration\Services\IntegrationService;
use App\Domains\Property\Models\Property;
use App\Domains\Reservation\Models\Reservation;
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

        $calendars = IcsCalendar::where('tenant_id', tenant_id())
            ->with('property', 'reservations')
            ->get();

        $properties = Property::where('tenant_id', tenant_id())->get();

        return view('panels.integrations.index', compact('integrations', 'calendars', 'properties'));
    }

    public function storeCalendar(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'provider' => 'required|in:airbnb,booking,other',
            'label' => 'required|string|max:100',
            'url' => 'required|url|max:2048',
            'color' => 'nullable|string|max:20',
        ]);

        $validated['tenant_id'] = tenant_id();
        $validated['is_active'] = true;

        $calendar = IcsCalendar::create($validated);

        return redirect()->back()->with('success', "Calendario «{$calendar->label}» creado");
    }

    public function updateCalendar(Request $request, IcsCalendar $calendar)
    {
        $this->authorizeCalendar($calendar);

        $validated = $request->validate([
            'provider' => 'required|in:airbnb,booking,other',
            'label' => 'required|string|max:100',
            'url' => 'required|url|max:2048',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $calendar->update($validated);

        return redirect()->back()->with('success', "Calendario «{$calendar->label}» actualizado");
    }

    public function destroyCalendar(Request $request, IcsCalendar $calendar)
    {
        $this->authorizeCalendar($calendar);

        $reservations = Reservation::where('ics_calendar_id', $calendar->id)->get();

        $toDelete = $reservations->filter(function ($res) {
            return $res->checkin_date >= now()->startOfDay() && $res->checkins()->count() === 0;
        });

        $toKeep = $reservations->filter(function ($res) {
            return $res->checkin_date < now()->startOfDay() || $res->checkins()->count() > 0;
        });

        $deletedCount = $toDelete->count();
        $keptCount = $toKeep->count();

        foreach ($toDelete as $res) {
            $res->delete();
        }

        $calendar->delete();

        $message = "Calendario «{$calendar->label}» eliminado.";
        if ($deletedCount > 0) {
            $message .= " {$deletedCount} reservas futuras eliminadas.";
        }
        if ($keptCount > 0) {
            $message .= " {$keptCount} reservas anteriores o con check-in conservadas.";
        }

        return redirect()->back()->with('success', $message);
    }

    public function syncCalendar(IcsCalendar $calendar)
    {
        $this->authorizeCalendar($calendar);

        $result = $this->integrationService->syncIcsCalendar($calendar);

        if ($result['success']) {
            $calendar->update([
                'last_sync_at' => now(),
                'last_sync_status' => 'ok',
                'last_error' => null,
                'last_sync_count' => $result['imported'],
            ]);
        } else {
            $calendar->update([
                'last_sync_at' => now(),
                'last_sync_status' => 'error',
                'last_error' => $result['error'],
            ]);
        }

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['success']
                ? "Calendario «{$calendar->label}» sincronizado: {$result['imported']} nuevas reservas importadas"
                : "Error al sincronizar «{$calendar->label}»: {$result['error']}"
        );
    }

    public function importIcs(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'file' => 'required|file|mimes:ics,txt|max:5120',
        ]);

        $property = Property::findOrFail($request->property_id);
        $content = file_get_contents($request->file('file')->getRealPath());
        $result = $this->integrationService->importIcs($content, $property);

        return redirect()->back()->with('success', "Importación completada: {$result['imported']} reservas importadas");
    }

    protected function authorizeCalendar(IcsCalendar $calendar): void
    {
        if ($calendar->tenant_id !== tenant_id()) {
            abort(403);
        }
    }
}
