<?php

return [
    'enabled' => env('SES_ENABLED', false),
    'username' => env('SES_USERNAME'),
    'password' => env('SES_PASSWORD'),
    'codigo_arrendador' => env('SES_ARRENDADOR_CODE'),
    'aplicacion' => env('SES_APP_NAME', 'Aldara'),
    'endpoint_pruebas' => 'https://hospedajes.pre-ses.mir.es/hospedajes-web/ws/v1/comunicacion',
    'endpoint_produccion' => 'https://hospedajes.ses.mir.es/hospedajes-web/ws/v1/comunicacion',
    'endpoint' => env('SES_ENDPOINT'),
    'timeout' => (int) env('SES_TIMEOUT', 30),
    'retry' => [
        'max_attempts' => 3,
        'delay_minutes' => 60,
    ],
    'payload' => [
        'required_fields' => [
            'nombre', 'apellido1', 'tipo_documento', 'numero_documento',
            'fecha_nacimiento', 'nacionalidad', 'sexo',
        ],
    ],
    'export' => [
        'format' => ['csv', 'json'],
        'default_format' => 'json',
    ],
    'max_comunicaciones_por_lote' => 100,
];
