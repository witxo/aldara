<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Rakibdevs\MrzParser\MrzParser;

class MrzController extends Controller
{
    public function parse(Request $request)
    {
        $request->validate([
            'text' => 'required|string|min:10|max:500',
        ]);

        $text = $request->input('text');
        $text = $this->_cleanMrzText($text);

        $result = MrzParser::tryParse($text);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo parsear el MRZ',
            ], 422);
        }

        $mapped = [
            'format' => null,
            'docType' => $this->mapType($result['type'] ?? ''),
            'documentNumber' => $result['card_no'] ?? '',
            'surname' => str_replace('<', ' ', $result['last_name'] ?? ''),
            'givenNames' => str_replace('<', ' ', $result['first_name'] ?? ''),
            'birthDate' => $result['date_of_birth'] ?? '',
            'expiryDate' => $result['date_of_expiry'] ?? '',
            'sex' => $result['sex'] ?? '',
            'nationality' => $result['nationality_code'] ?? '',
            'country' => $result['issuer_code'] ?? '',
            'personalNumber' => $result['personal_number'] ?? '',
            'valid' => $result['valid'] ?? false,
            'validation' => $result['validation'] ?? [],
            'confidence' => 1.0,
        ];

        switch ($result['type'] ?? '') {
            case 'Travel Document':
                $mapped['format'] = 'TD1';
                break;
            case 'Passport':
                $mapped['format'] = 'TD3';
                break;
            case 'Visa':
                $mapped['format'] = 'TD2';
                break;
        }

        return response()->json([
            'success' => true,
            'data' => $mapped,
        ]);
    }

    private function _cleanMrzText(string $text): string
    {
        $lines = explode("\n", $text);
        $lines = array_map(function ($l) {
            return preg_replace('/[^A-Z0-9<]/', '', strtoupper(trim($l)));
        }, $lines);
        $lines = array_values(array_filter($lines, function ($l) {
            return strlen($l) >= 20;
        }));

        if (empty($lines)) return $text;

        if (strlen($lines[0]) >= 3 &&
            !in_array($lines[0][0], ['I', 'P', 'A', 'C', 'V']) &&
            substr($lines[0], 1, 3) === 'ESP') {
            $lines[0] = 'I' . $lines[0];
        }

        // Apply noise cleaning to ALL lines, not just line 3
        foreach ($lines as $i => &$line) {
            // SS -> << (common misread of double filler)
            $line = preg_replace('/SS/', '<<', $line);
            // KL/LK/KK/LL runs -> << (runs of noise chars where filler should be)
            $line = preg_replace('/[KL]{2,}/', '<<', $line);
            // Single K/L between < chars -> <
            $line = preg_replace('/<[KL]/', '<<', $line);
            $line = preg_replace('/[KL]</', '<<', $line);
            // D between < chars -> <
            $line = preg_replace('/<D/', '<<', $line);
            // Leading D after I at position 1 (I<ESP misread as IDESP)
            if ($i === 0 && str_starts_with($line, 'ID')) {
                $line = 'I<' . substr($line, 2);
            }
        }
        unset($line);

        // Aggressive: in the first two lines, convert sequences of 3+ noise chars
        // Typically the filler region at the end gets read as K/L/D instead of <
        for ($i = 0; $i < min(2, count($lines)); $i++) {
            // If the line has more than 30 chars, convert trailing cluster of
            // A-Z chars (excluding leading fields) to fillers
            if (strlen($lines[$i]) > 30) {
                // From position 28 onward, any remaining K/L/D -> <
                $tail = substr($lines[$i], 28);
                $tail = preg_replace('/[KLD]/', '<', $tail);
                $lines[$i] = substr($lines[$i], 0, 28) . $tail;
            }
        }

        // Trim or pad each line to exactly 30 chars for TD1 format
        for ($i = 0; $i < min(3, count($lines)); $i++) {
            if (strlen($lines[$i]) > 30) {
                $lines[$i] = substr($lines[$i], 0, 30);
            } elseif (strlen($lines[$i]) < 30) {
                $lines[$i] = str_pad($lines[$i], 30, '<');
            }
        }

        return implode("\n", array_slice($lines, 0, 3));
    }

    private function mapType(?string $type): string
    {
        $map = [
            'Travel Document' => 'dni',
            'Passport' => 'passport',
            'Visa' => 'visa',
        ];
        return $map[$type] ?? 'unknown';
    }
}