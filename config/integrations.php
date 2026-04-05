<?php
return [
    'payments' => [
        'default' => env('PAYMENT_DEFAULT_PROVIDER', 'debito_mpesa'),
        'debito' => [
            'base_url' => env('DEBITO_BASE_URL', ''),
            'email' => env('DEBITO_EMAIL', ''),
            'password' => env('DEBITO_PASSWORD', ''),
            'wallet_id' => env('DEBITO_WALLET_ID', ''),
            'timeout' => (int) env('DEBITO_TIMEOUT', 20),
            'status_polling_enabled' => (bool) env('DEBITO_STATUS_POLLING_ENABLED', true),
            'status_polling_interval' => (int) env('DEBITO_STATUS_POLLING_INTERVAL', 60),
        ],
    ],
    'whatsapp' => [
        'default' => env('WHATSAPP_DEFAULT_PROVIDER', 'mock'),
    ],
    'ai' => [
        'default' => env('AI_DEFAULT_PROVIDER', 'openrouter'),
    ],
];
