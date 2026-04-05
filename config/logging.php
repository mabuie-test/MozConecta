<?php
return [
    'default' => env('LOG_CHANNEL', 'single'),
    'path' => base_path('storage/logs/app.log'),
    'level' => env('LOG_LEVEL', 'debug'),
];
