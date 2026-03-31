<?php
return [
    'payments' => [
        'default' => env('PAYMENT_DEFAULT_PROVIDER', 'mpesa'),
    ],
    'whatsapp' => [
        'default' => env('WHATSAPP_DEFAULT_PROVIDER', 'mock'),
    ],
    'ai' => [
        'default' => env('AI_DEFAULT_PROVIDER', 'openrouter'),
    ],
];
