<?php

return [
    'default_trial_days' => env('TENANT_DEFAULT_TRIAL_DAYS', 14),
    'session_tenant_key' => 'tenant_id',
    'allowed_impersonation' => env('TENANT_ALLOW_IMPERSONATION', false),
    'subscription' => [
        'grace_period_days' => 7,
        'block_features_when_past_due' => [
            'checkin', 'ses', 'integrations', 'billing'
        ],
    ],
];
