<?php

namespace App\Domains\Integration\Services;

use App\Domains\Integration\Models\PropertyIntegration;
use App\Domains\Integration\Models\IcsCalendar;
use App\Domains\Integration\Connectors\Contracts\ConnectorInterface;
use App\Domains\Integration\Connectors\IcsImporter;
use App\Domains\Property\Models\Property;
use App\Domains\Reservation\Models\Reservation;
use Illuminate\Support\Facades\Http;

class IntegrationService
{
    public function getConnector(string $provider): ?ConnectorInterface
    {
        $connectors = [
            'booking' => app('connector.booking'),
            'airbnb' => app('connector.airbnb'),
        ];

        return $connectors[$provider] ?? null;
    }

    public function syncReservations(PropertyIntegration $integration): array
    {
        $connector = $this->getConnector($integration->provider);
        if (!$connector) {
            return ['success' => false, 'error' => "Conector no encontrado: {$integration->provider}"];
        }

        $result = $connector->syncReservations($integration);

        if (!$result->success) {
            $integration->update([
                'sync_status' => 'error',
                'last_sync_at' => now(),
            ]);
            return ['success' => false, 'error' => $result->error];
        }

        $imported = 0;
        foreach ($result->reservations as $externalReservation) {
            $exists = Reservation::where('external_code', $externalReservation->externalId)
                ->where('property_id', $integration->property_id)
                ->exists();

            if ($exists) {
                continue;
            }

            Reservation::create([
                'tenant_id' => $integration->tenant_id,
                'property_id' => $integration->property_id,
                'code' => $externalReservation->externalId,
                'external_code' => $externalReservation->externalId,
                'source' => $integration->provider,
                'status' => 'confirmed',
                'guest_name' => $externalReservation->guestName,
                'guest_email' => $externalReservation->guestEmail,
                'guest_phone' => $externalReservation->guestPhone,
                'adults' => $externalReservation->adults,
                'children' => $externalReservation->children,
                'checkin_date' => $externalReservation->checkinDate,
                'checkout_date' => $externalReservation->checkoutDate,
                'channel_data' => $externalReservation->rawData,
            ]);
            $imported++;
        }

        $integration->update([
            'sync_status' => 'ok',
            'last_sync_at' => now(),
        ]);

        return [
            'success' => true,
            'imported' => $imported,
            'duplicates' => $result->duplicates,
            'total' => $result->imported,
        ];
    }

    public function syncIcsCalendar(IcsCalendar $calendar): array
    {
        try {
            $response = Http::timeout(30)->get($calendar->url);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => "HTTP {$response->status()} al obtener la URL",
                    'imported' => 0,
                    'total' => 0,
                ];
            }

            $icsContent = $response->body();
            $importer = new IcsImporter();
            $reservations = $importer->import($icsContent, $calendar->property, $calendar->provider);

            $imported = 0;
            foreach ($reservations as $data) {
                $exists = Reservation::where('external_code', $data['external_code'])
                    ->where('property_id', $calendar->property_id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $data['tenant_id'] = $calendar->tenant_id;
                $data['property_id'] = $calendar->property_id;
                $data['ics_calendar_id'] = $calendar->id;
                $data['code'] = $data['external_code'] ?? 'ICS-' . strtoupper(substr(md5(rand()), 0, 8));

                Reservation::create($data);
                $imported++;
            }

            return [
                'success' => true,
                'imported' => $imported,
                'total' => count($reservations),
                'error' => null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'imported' => 0,
                'total' => 0,
            ];
        }
    }

    public function importIcs(string $icsContent, Property $property): array
    {
        $importer = new \App\Domains\Integration\Connectors\IcsImporter();
        $reservations = $importer->import($icsContent, $property);

        $imported = 0;
        foreach ($reservations as $data) {
            $exists = Reservation::where('external_code', $data['external_code'])
                ->where('property_id', $property->id)
                ->exists();

            if ($exists) {
                continue;
            }

            $data['tenant_id'] = $property->tenant_id;
            $data['property_id'] = $property->id;
            $data['code'] = $data['external_code'] ?? 'ICS-' . strtoupper(substr(md5(rand()), 0, 8));

            Reservation::create($data);
            $imported++;
        }

        return [
            'success' => true,
            'imported' => $imported,
            'total' => count($reservations),
        ];
    }
}
