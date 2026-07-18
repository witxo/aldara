<?php

namespace App\Domains\Integration\Connectors\Contracts;

use App\Domains\Integration\Models\PropertyIntegration;

interface ConnectorInterface
{
    public function connect(PropertyIntegration $config): array;
    public function disconnect(PropertyIntegration $config): bool;
    public function syncReservations(PropertyIntegration $config): SyncResult;
    public function getReservation(string $externalId): ?ExternalReservation;
    public function supports(): array;
    public function getName(): string;
}

class SyncResult
{
    public function __construct(
        public readonly bool $success,
        public readonly array $reservations = [],
        public readonly int $imported = 0,
        public readonly int $duplicates = 0,
        public readonly ?string $error = null,
    ) {}
}

class ExternalReservation
{
    public function __construct(
        public readonly string $externalId,
        public readonly string $guestName,
        public readonly string $checkinDate,
        public readonly string $checkoutDate,
        public readonly int $adults,
        public readonly int $children,
        public readonly string $status,
        public readonly ?string $guestEmail = null,
        public readonly ?string $guestPhone = null,
        public readonly ?array $rawData = null,
    ) {}
}
