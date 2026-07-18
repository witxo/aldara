<?php

return [
    'token_expiry_hours' => (int) env('CHECKIN_TOKEN_EXPIRY_HOURS', 48),
    'max_guests' => (int) env('CHECKIN_MAX_GUESTS', 20),
    'require_signature' => (bool) env('CHECKIN_REQUIRE_SIGNATURE', true),
    'require_document_upload' => (bool) env('CHECKIN_REQUIRE_DOCUMENT', false),
    'allowed_document_types' => ['dni', 'nie', 'passport', 'other'],
    'consent' => [
        'legal_required' => true,
        'marketing_optional' => true,
        'data_retention_required' => true,
    ],
    'allowed_upload_mimes' => ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'],
    'max_upload_size_kb' => 5120,
];
