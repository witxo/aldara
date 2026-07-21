<?php

namespace App\Domains\Compliance\Services;

use App\Domains\Reservation\Models\Reservation;

class SesPayloadBuilder
{
    public function __construct(
        protected SesXmlBuilder $xmlBuilder,
    ) {}

    public function build(Reservation $reservation, ?array $guestsData = null): array
    {
        $xml = $this->xmlBuilder->buildAltaPartesViajeros($reservation, $guestsData);

        $zip = new \ZipArchive();
        $tmpFile = tempnam(sys_get_temp_dir(), 'ses_');
        $zip->open($tmpFile, \ZipArchive::CREATE);
        $zip->addFromString('comunicacion.xml', $xml);
        $zip->close();
        $zipped = file_get_contents($tmpFile);
        unlink($tmpFile);
        $base64 = base64_encode($zipped);

        $property = $reservation->property;
        $guests = $guestsData ?: $reservation->guests->toArray();

        return [
            'xml' => $xml,
            'zip_base64' => $base64,
            'reservation' => [
                'fecha_entrada' => $reservation->checkin_date->format('Y-m-d'),
                'fecha_salida' => $reservation->checkout_date->format('Y-m-d'),
                'num_adultos' => $reservation->adults,
                'num_menores' => $reservation->children,
            ],
            'establecimiento_code' => $property->ses_establecimiento_code,
            'guest_count' => count($guests),
        ];
    }
}
