<?php

namespace App\Domains\Integration\Connectors;

use App\Domains\Property\Models\Property;
use App\Domains\Reservation\Models\Reservation;

class IcsImporter
{
    public function import(string $icsContent, Property $property, string $provider = 'booking'): array
    {
        $unfolded = $this->unfoldLines($icsContent);
        $events = $this->extractEvents($unfolded);
        $reservations = [];

        foreach ($events as $event) {
            $reservation = $this->parseEvent($event, $provider);
            if ($reservation) {
                $reservations[] = $reservation;
            }
        }

        return $this->deduplicate($reservations, $property);
    }

    protected function unfoldLines(string $content): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $unfolded = [];

        foreach ($lines as $line) {
            if ($line === '' || $line === "\r") continue;
            if (in_array(trim($line), ['BEGIN:VCALENDAR', 'END:VCALENDAR'])) {
                $unfolded[] = trim($line);
                continue;
            }
            if (isset($line[0]) && ($line[0] === ' ' || $line[0] === "\t")) {
                if (!empty($unfolded)) {
                    $unfolded[count($unfolded) - 1] .= ltrim($line, " \t");
                }
            } else {
                $unfolded[] = trim($line);
            }
        }

        return implode("\n", $unfolded);
    }

    protected function extractEvents(string $content): array
    {
        $events = [];
        $lines = explode("\n", $content);
        $currentEvent = null;
        $inEvent = false;

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === 'BEGIN:VEVENT') {
                $currentEvent = [];
                $inEvent = true;
                continue;
            }

            if ($line === 'END:VEVENT' && $inEvent && $currentEvent !== null) {
                $events[] = $currentEvent;
                $currentEvent = null;
                $inEvent = false;
                continue;
            }

            if ($inEvent && $currentEvent !== null) {
                $colonPos = strpos($line, ':');
                if ($colonPos === false) continue;

                $name = substr($line, 0, $colonPos);
                $value = substr($line, $colonPos + 1);

                if (str_starts_with($name, 'DTSTART')) {
                    $currentEvent['DTSTART'] = $this->extractDateValue($value);
                } elseif (str_starts_with($name, 'DTEND')) {
                    $currentEvent['DTEND'] = $this->extractDateValue($value);
                } elseif ($name === 'SUMMARY') {
                    $currentEvent['SUMMARY'] = $value;
                } elseif ($name === 'DESCRIPTION') {
                    $currentEvent['DESCRIPTION'] = $value;
                } elseif ($name === 'UID') {
                    $currentEvent['UID'] = $value;
                } elseif ($name === 'LOCATION') {
                    $currentEvent['LOCATION'] = $value;
                }
            }
        }

        return $events;
    }

    protected function extractDateValue(string $value): string
    {
        if (str_starts_with($value, ';')) {
            $parts = explode(':', $value, 2);
            $value = $parts[1] ?? $parts[0];
        }

        $value = trim($value);

        if (preg_match('/^(\d{4})(\d{2})(\d{2})/', $value, $m)) {
            return $m[1] . '-' . $m[2] . '-' . $m[3];
        }

        return $value;
    }

    protected function parseEvent(array $event, string $provider): ?array
    {
        if (!isset($event['DTSTART']) || !isset($event['DTEND'])) {
            return null;
        }

        $guestName = $this->extractGuestName($event);
        $guestEmail = $this->extractEmail($event);
        $guestPhone = $this->extractPhone($event);
        $adults = $this->extractAdults($event);

        return [
            'external_code' => $event['UID'] ?? null,
            'guest_name' => $guestName,
            'guest_email' => $guestEmail,
            'guest_phone' => $guestPhone,
            'checkin_date' => $event['DTSTART'],
            'checkout_date' => $event['DTEND'],
            'source' => $provider,
            'status' => 'confirmed',
            'adults' => $adults,
            'children' => 0,
        ];
    }

    protected function extractGuestName(array $event): string
    {
        $summary = $event['SUMMARY'] ?? '';

        if ($summary === '') {
            return 'Huésped (ICS)';
        }

        $prefixes = ['Reserva de ', 'Reserva: ', 'Booking: ', 'New Booking: ', 'Booking.com: ', 'Airbnb: '];
        foreach ($prefixes as $prefix) {
            if (str_starts_with($summary, $prefix)) {
                return trim(substr($summary, strlen($prefix)));
            }
        }

        return $summary;
    }

    protected function extractEmail(array $event): ?string
    {
        $desc = $event['DESCRIPTION'] ?? '';
        if (preg_match('/EMAIL[:\s]+([^\s,;]+@[^\s,;]+)/i', $desc, $m)) {
            return $m[1];
        }
        return null;
    }

    protected function extractPhone(array $event): ?string
    {
        $desc = $event['DESCRIPTION'] ?? '';
        if (preg_match('/TEL[:\s]+([+\d\s\-\(\)]+?)(?:\s|,|;|$)/i', $desc, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    protected function extractAdults(array $event): int
    {
        $desc = $event['DESCRIPTION'] ?? '';
        if (preg_match('/(\d+)\s*(?:adultos?|adults?|pax|personas?|guests?)/i', $desc, $m)) {
            return (int) $m[1];
        }
        return 1;
    }

    protected function deduplicate(array $reservations, Property $property): array
    {
        $existing = Reservation::where('property_id', $property->id)
            ->whereIn('status', ['confirmed', 'checkin_sent', 'pending'])
            ->get(['external_code', 'guest_name', 'checkin_date', 'checkout_date']);

        $byCode = [];
        $byDetails = [];

        foreach ($existing as $res) {
            if ($res['external_code']) {
                $byCode[$res['external_code']] = true;
            }
            $key = strtolower($res['guest_name'] . '|' . $res['checkin_date'] . '|' . $res['checkout_date']);
            $byDetails[$key] = true;
        }

        return array_filter($reservations, function ($res) use ($byCode, $byDetails) {
            if ($res['external_code'] && isset($byCode[$res['external_code']])) {
                return false;
            }
            $key = strtolower($res['guest_name'] . '|' . $res['checkin_date'] . '|' . $res['checkout_date']);
            if (isset($byDetails[$key])) {
                return false;
            }
            return true;
        });
    }
}
