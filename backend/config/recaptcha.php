<?php

return [
    'enabled' => env('RECAPTCHA_V3_ENABLED', true),

    'site_key' => env('RECAPTCHA_V3_SITE_KEY', ''),

    'secret_key' => env('RECAPTCHA_V3_SECRET_KEY', ''),

    'threshold' => env('RECAPTCHA_V3_THRESHOLD', 0.5),

    'actions' => [
        'contact' => 'contact',
        'login' => 'login',
        'register' => 'register',
        'forgot_password' => 'forgot_password',
        'reset_password' => 'reset_password',
        'checkin_submit' => 'checkin_submit',
        'checkin_search' => 'checkin_search',
    ],

    'timeout' => 10,

    'fail_open' => env('RECAPTCHA_V3_FAIL_OPEN', true),
];