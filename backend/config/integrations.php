<?php

return [
    'booking' => [
        'enabled' => env('BOOKING_ENABLED', false),
        'api_base_url' => env('BOOKING_API_BASE', 'https://supply-xml.booking.com'),
        'api_key' => env('BOOKING_API_KEY'),
        'api_secret' => env('BOOKING_API_SECRET'),
        'timeout' => 30,
        'polling_interval_minutes' => 15,
    ],
    'airbnb' => [
        'enabled' => env('AIRBNB_ENABLED', false),
        'api_base_url' => env('AIRBNB_API_BASE', 'https://api.airbnb.com/v2'),
        'client_id' => env('AIRBNB_CLIENT_ID'),
        'client_secret' => env('AIRBNB_CLIENT_SECRET'),
        'timeout' => 30,
        'polling_interval_minutes' => 15,
    ],
    'ics' => [
        'import_enabled' => true,
        'max_file_size_mb' => 5,
        'allowed_mime_types' => ['text/calendar', 'text/plain'],
    ],
    'pms' => [
        'adapters' => [
            'guiali' => ['class' => \App\Domains\Integration\Connectors\MockPmsConnector::class],
            'lodgify' => ['class' => \App\Domains\Integration\Connectors\MockPmsConnector::class],
            'hostify' => ['class' => \App\Domains\Integration\Connectors\MockPmsConnector::class],
        ],
    ],
];
