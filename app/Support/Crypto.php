<?php
declare(strict_types=1);

namespace App\Support;

final class Crypto
{
    public static function encrypt(string $plain): string
    {
        $key = hash('sha256', (string)env('APP_KEY', 'change-this'), true);
        $iv = random_bytes(16);
        $cipher = openssl_encrypt($plain, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . ($cipher ?: ''));
    }

    public static function decrypt(string $encoded): string
    {
        $raw = base64_decode($encoded, true);
        if ($raw === false || strlen($raw) < 17) {
            return '';
        }
        $key = hash('sha256', (string)env('APP_KEY', 'change-this'), true);
        $iv = substr($raw, 0, 16);
        $cipher = substr($raw, 16);
        return (string)openssl_decrypt($cipher, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }
}
