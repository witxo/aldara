<?php

namespace App\Domains\Integration\Connectors;

use App\Domains\Integration\Connectors\Contracts\ConnectorInterface;
use App\Domains\Integration\Connectors\Contracts\SyncResult;
use App\Domains\Integration\Connectors\Contracts\ExternalReservation;
use App\Domains\Integration\Models\PropertyIntegration;

class MockBookingConnector implements ConnectorInterface
{
    public function getName(): string
    {
        return 'Booking.com';
    }

    public function connect(PropertyIntegration $config): array
    {
        return [
            'success' => true,
            'message' => 'Conexión simulada con Booking.com. Requiere verificación con API real.',
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
                externalId: 'BK-' . strtoupper(substr(md5(rand()), 0, 8)),
                guestName: 'Juan Pérez (mock)',
                checkinDate: now()->addDays(rand(1, 14))->format('Y-m-d'),
                checkoutDate: now()->addDays(rand(3, 10))->format('Y-m-d'),
                adults: 2,
                children: 0,
                status: 'confirmed',
                guestEmail: 'juan.perez@example.com',
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
        return ['polling', 'webhook'];
    }
}
