<?php
return [
    'payments' => [
        'default' => env('PAYMENT_DEFAULT_PROVIDER', 'debito_mpesa'),
        'debito' => [
            'base_url' => env('DEBITO_BASE_URL', 'https://my.debito.co.mz'),
            'token' => env('DEBITO_TOKEN', ''),
            'email' => env('DEBITO_EMAIL', ''),
            'password' => env('DEBITO_PASSWORD', ''),
            'wallet_id_mpesa' => env('DEBITO_WALLET_ID_MPESA', ''),
            'wallet_id_emola' => env('DEBITO_WALLET_ID_EMOLA', ''),
            'callback_url' => env('DEBITO_CALLBACK_URL', ''),
            'timeout' => (int) env('DEBITO_TIMEOUT', 30),
            'retry_count' => (int) env('DEBITO_RETRY_COUNT', 3),
            'status_polling_enabled' => filter_var((string) env('DEBITO_STATUS_POLLING_ENABLED', 'true'), FILTER_VALIDATE_BOOLEAN),
            'status_polling_interval' => (int) env('DEBITO_STATUS_POLLING_INTERVAL', 60),
        ],
    ],
    'whatsapp' => [
        'default' => env('WHATSAPP_PROVIDER_DEFAULT', 'generic_api'),
        'api_base_url' => env('WHATSAPP_API_BASE_URL', ''),
        'api_key' => env('WHATSAPP_API_KEY', ''),
        'webhook_secret' => env('WHATSAPP_WEBHOOK_SECRET', ''),
        'sync_enabled' => (bool) env('WHATSAPP_SYNC_ENABLED', true),
        'sync_interval' => (int) env('WHATSAPP_SYNC_INTERVAL', 120),
    ],
    'ai' => [
        'default' => env('AI_DEFAULT_PROVIDER', 'openrouter'),
        'timeout' => (int) env('AI_TIMEOUT', 25),
        'openrouter' => [
            'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
            'api_key' => env('OPENROUTER_API_KEY', ''),
            'model' => env('OPENROUTER_MODEL', 'openai/gpt-4o-mini'),
        ],
        'gemini' => [
            'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
            'api_key' => env('GEMINI_API_KEY', ''),
            'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
        ],
    ],
];
