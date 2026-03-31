<?php
return [
    'lifetime' => (int) env('SESSION_LIFETIME', 120),
    'secure' => (bool) env('SESSION_SECURE_COOKIE', false),
    'same_site' => env('SESSION_SAME_SITE', 'Lax'),
];
