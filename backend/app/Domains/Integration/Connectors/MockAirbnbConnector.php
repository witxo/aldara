<?php

namespace App\Domains\Integration\Connectors;

use App\Domains\Integration\Connectors\Contracts\ConnectorInterface;
use App\Domains\Integration\Connectors\Contracts\SyncResult;
use App\Domains\Integration\Connectors\Contracts\ExternalReservation;
use App\Domains\Integration\Models\PropertyIntegration;

class MockAirbnbConnector implements ConnectorInterface
{
    public function getName(): string
    {
        return 'Airbnb';
    }

    public function connect(PropertyIntegration $config): array
    {
        return [
            'success' => true,
            'message' => 'Conexión simulada con Airbnb. Requiere verificación con API real.',
        ];
    }

    public function disconnect(PropertyIntegration $config): bool
    {
        return true;
    }

    public function syncReservations(PropertyIntegration $config): SyncResult
    {
        $mockReservations = [
            new ExternalReservation(
                externalId: 'AB-' . strtoupper(substr(md5(rand()), 0, 8)),
                guestName: 'Maria García (mock)',
                checkinDate: now()->addDays(rand(1, 10))->format('Y-m-d'),
                checkoutDate: now()->addDays(rand(4, 8))->format('Y-m-d'),
                adults: 1,
                children: 2,
                status: 'confirmed',
                guestEmail: 'maria.garcia@example.com',
            ),
        ];

        return new SyncResult(
            success: true,
            reservations: $mockReservations,
            imported: count($mockReservations),
            duplicates: 0,
            error: null,
        );
    }

    public function getReservation(string $externalId): ?ExternalReservation
    {
        return null;
    }

    public function supports(): array
    {
        return ['polling', 'ics'];
    }
}
