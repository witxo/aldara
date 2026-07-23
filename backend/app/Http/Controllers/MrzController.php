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

        // Clean name line (third line for TD1): convert common OCR noise separators
        if (isset($lines[2])) {
            $lines[2] = preg_replace('/SS/', '<<', $lines[2]);
            $lines[2] = preg_replace('/[KL]{2,}/', '<<', $lines[2]);
            $lines[2] = preg_replace('/<[KL]/', '<<', $lines[2]);
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