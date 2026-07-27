<?php

namespace App\Domains\Compliance\Services;

use App\Domains\Reservation\Models\Reservation;
use App\Domains\Guest\Models\Guest;
use DOMDocument;
use DOMElement;

class SesXmlBuilder
{
    public function buildAltaPartesViajeros(Reservation $reservation, array $guestsData = null): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $peticion = $dom->createElementNS('http://www.neg.hospedajes.mir.es/altaParteHospedaje', 'alt:peticion');
        $dom->appendChild($peticion);

        $solicitud = $dom->createElement('solicitud');
        $peticion->appendChild($solicitud);

        $property = $reservation->property;
        $codEst = $dom->createElement('codigoEstablecimiento', $property->ses_establecimiento_code ?? '');
        $solicitud->appendChild($codEst);

        $guests = $guestsData
            ? $this->buildGuestsFromRequest($guestsData, $reservation)
            : $this->buildGuestsFromModel($reservation);

        foreach ($guests as $index => $guestData) {
            $comunicacion = $dom->createElement('comunicacion');
            $solicitud->appendChild($comunicacion);

            $contrato = $dom->createElement('contrato');
            $comunicacion->appendChild($contrato);

            $this->appendElement($contrato, 'referencia', $reservation->code ?? $reservation->uuid);
            $this->appendElement($contrato, 'fechaContrato', $reservation->created_at->format('Y-m-d'));
            $this->appendElement($contrato, 'fechaEntrada', $reservation->checkin_date->format('Y-m-d') . 'T' . ($reservation->checkin_time?->format('H:i:s') ?? '00:00:00'));
            $this->appendElement($contrato, 'fechaSalida', $reservation->checkout_date->format('Y-m-d') . 'T' . ($reservation->checkout_time?->format('H:i:s') ?? '00:00:00'));
            $this->appendElement($contrato, 'numPersonas', (string) ($reservation->adults + $reservation->children));

            if ($reservation->adults + $reservation->children > 0) {
                $this->appendElement($contrato, 'numHabitaciones', '1');
            }

            $this->appendElement($contrato, 'internet', 'false');

            $pago = $dom->createElement('pago');
            $contrato->appendChild($pago);
            $this->appendElement($pago, 'tipoPago', 'OTRO');
            $this->appendElement($pago, 'fechaPago', $reservation->created_at->format('Y-m-d'));

            $persona = $dom->createElement('persona');
            $comunicacion->appendChild($persona);

            $this->appendElement($persona, 'rol', 'VI');

            $this->appendElement($persona, 'nombre', $guestData['nombre']);
            $this->appendElement($persona, 'apellido1', $guestData['apellido1']);

            if (!empty($guestData['apellido2'])) {
                $this->appendElement($persona, 'apellido2', $guestData['apellido2']);
            }

            if (!empty($guestData['tipo_documento']) && !empty($guestData['numero_documento'])) {
                $this->appendElement($persona, 'tipoDocumento', $guestData['tipo_documento']);
                $this->appendElement($persona, 'numeroDocumento', $guestData['numero_documento']);
                if (!empty($guestData['soporte_documento'])) {
                    $this->appendElement($persona, 'soporteDocumento', $guestData['soporte_documento']);
                }
            }

            if (!empty($guestData['fecha_nacimiento'])) {
                $this->appendElement($persona, 'fechaNacimiento', $guestData['fecha_nacimiento']);
            }

            if (!empty($guestData['nacionalidad'])) {
                $this->appendElement($persona, 'nacionalidad', $guestData['nacionalidad']);
            }

            if (!empty($guestData['sexo'])) {
                $this->appendElement($persona, 'sexo', $guestData['sexo']);
            }

            $direccion = $dom->createElement('direccion');
            $persona->appendChild($direccion);
            $this->appendElement($direccion, 'direccion', $guestData['direccion'] ?? '');
            $this->appendElement($direccion, 'codigoPostal', $guestData['codigo_postal'] ?? '28001');
            $this->appendElement($direccion, 'pais', $guestData['pais'] ?? 'ESP');

            if (!empty($guestData['telefono'])) {
                $this->appendElement($persona, 'telefono', $guestData['telefono']);
            }

            if (!empty($guestData['email'])) {
                $this->appendElement($persona, 'correo', $guestData['email']);
            }

            if (!empty($guestData['parentesco'])) {
                $this->appendElement($persona, 'parentesco', $guestData['parentesco']);
            }
        }

        return $dom->saveXML();
    }

    protected function buildGuestsFromModel(Reservation $reservation): array
    {
        return $reservation->guests->map(function (Guest $guest) use ($reservation) {
            return $this->guestToArray($guest, $reservation);
        })->values()->toArray();
    }

    protected function buildGuestsFromRequest(array $guestsData, Reservation $reservation): array
    {
        $guests = [];
        foreach ($guestsData as $g) {
            $guests[] = $this->requestToArray($g, $reservation);
        }
        return $guests;
    }

    protected function guestToArray(Guest $guest, Reservation $reservation): array
    {
        $isMinor = $guest->birth_date && $guest->birth_date->age < 18;

        return [
            'nombre' => $guest->first_name,
            'apellido1' => $guest->last_name,
            'apellido2' => '',
            'tipo_documento' => $this->mapDocumentType($guest->document_type),
            'numero_documento' => $guest->document_number,
            'soporte_documento' => $guest->document_support ?? '',
            'fecha_nacimiento' => $guest->birth_date?->format('Y-m-d'),
            'nacionalidad' => $this->mapNationality($guest->nationality ?? ''),
            'sexo' => $this->mapGender($guest->gender),
            'direccion' => $guest->address_line1 ?? $guest->address ?? '',
            'codigo_postal' => $guest->address_postal_code ?? '28001',
            'pais' => strtoupper($guest->address_country ?? 'ESP'),
            'telefono' => $guest->phone ?? $reservation->guest_phone,
            'email' => $guest->email ?? $reservation->guest_email,
            'parentesco' => $isMinor ? ($guest->parentesco ?? 'OTRO') : null,
        ];
    }

    protected function requestToArray(array $data, Reservation $reservation): array
    {
        return [
            'nombre' => $data['first_name'] ?? '',
            'apellido1' => $data['last_name'] ?? '',
            'apellido2' => $data['last_name2'] ?? '',
            'tipo_documento' => $this->mapDocumentType($data['document_type'] ?? ''),
            'numero_documento' => $data['document_number'] ?? '',
            'soporte_documento' => $data['document_support'] ?? '',
            'fecha_nacimiento' => $data['birth_date'] ?? null,
            'nacionalidad' => $this->mapNationality($data['nationality'] ?? ''),
            'sexo' => $this->mapGender($data['gender'] ?? ''),
            'direccion' => $data['address_line1'] ?? $data['address'] ?? '',
            'codigo_postal' => $data['address_postal_code'] ?? '28001',
            'pais' => strtoupper($data['address_country'] ?? 'ESP'),
            'telefono' => $data['phone'] ?? $reservation->guest_phone,
            'email' => $data['email'] ?? $reservation->guest_email,
            'parentesco' => $data['parentesco'] ?? null,
        ];
    }

    protected function mapDocumentType(?string $type): string
    {
        return match (strtolower($type ?? '')) {
            'dni', 'nif' => 'CP',
            'nie' => 'TI',
            'passport', 'pasaporte' => 'VI',
            default => 'VI',
        };
    }

    protected function mapGender(?string $gender): string
    {
        return match (strtolower($gender ?? '')) {
            'male', 'm', 'h', 'hombre' => 'H',
            'female', 'f', 'mujer' => 'M',
            default => '',
        };
    }

    protected function mapNationality(string $code): string
    {
        $map = [
            'ES' => 'ESP', 'FR' => 'FRA', 'GB' => 'GBR', 'DE' => 'DEU',
            'IT' => 'ITA', 'PT' => 'PRT', 'BE' => 'BEL', 'NL' => 'NLD',
            'CH' => 'CHE', 'AT' => 'AUT', 'DK' => 'DNK', 'SE' => 'SWE',
            'NO' => 'NOR', 'FI' => 'FIN', 'GR' => 'GRC', 'IE' => 'IRL',
            'PL' => 'POL', 'CZ' => 'CZE', 'HU' => 'HUN', 'RO' => 'ROU',
            'BG' => 'BGR', 'HR' => 'HRV', 'SK' => 'SVK', 'SI' => 'SVN',
            'LT' => 'LTU', 'LV' => 'LVA', 'EE' => 'EST', 'US' => 'USA',
            'CA' => 'CAN', 'MX' => 'MEX', 'BR' => 'BRA', 'AR' => 'ARG',
            'CL' => 'CHL', 'CO' => 'COL', 'PE' => 'PER', 'JP' => 'JPN',
            'CN' => 'CHN', 'IN' => 'IND', 'RU' => 'RUS', 'TR' => 'TUR',
            'AU' => 'AUS', 'NZ' => 'NZL', 'MA' => 'MAR', 'DZ' => 'DZA',
            'TN' => 'TUN', 'EG' => 'EGY', 'ZA' => 'ZAF',
        ];
        return $map[strtoupper($code)] ?? 'OTR';
    }

    protected function appendElement(DOMElement $parent, string $name, string $value = null): void
    {
        $doc = $parent->ownerDocument;
        $element = $doc->createElement($name);
        if ($value !== null && $value !== '') {
            $element->appendChild($doc->createTextNode($value));
        }
        $parent->appendChild($element);
    }
}
