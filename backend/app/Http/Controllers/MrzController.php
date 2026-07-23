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
            'surname' => $result['last_name'] ?? '',
            'givenNames' => $result['first_name'] ?? '',
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