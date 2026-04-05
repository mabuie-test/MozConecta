<?php
declare(strict_types=1);

namespace App\Integrations\Payments;

use App\Support\Logger;

final class DebitoAuthService
{
    private const TOKEN_CACHE_FILE = '/storage/cache/debito_token.json';

    public function __construct(private readonly Logger $logger)
    {
    }

    public function getToken(): string
    {
        $cached = $this->readCachedToken();
        if ($cached && ($cached['expires_at'] ?? 0) > time() + 30) {
            return (string)$cached['token'];
        }

        $response = $this->login();
        $token = (string)($response['token'] ?? $response['access_token'] ?? '');
        if ($token === '') {
            throw new \RuntimeException('Falha ao autenticar na API Débito.');
        }

        $expiresIn = (int)($response['expires_in'] ?? 3600);
        $this->cacheToken($token, time() + $expiresIn);
        return $token;
    }

    private function login(): array
    {
        $client = new DebitoClient($this->logger, $this, false);
        return $client->request('POST', '/api/v1/login', [
            'email' => env('DEBITO_EMAIL', ''),
            'password' => env('DEBITO_PASSWORD', ''),
        ], false);
    }

    private function cacheToken(string $token, int $expiresAt): void
    {
        $path = base_path(self::TOKEN_CACHE_FILE);
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }
        file_put_contents($path, json_encode(['token' => $token, 'expires_at' => $expiresAt]));
    }

    private function readCachedToken(): ?array
    {
        $path = base_path(self::TOKEN_CACHE_FILE);
        if (!is_file($path)) {
            return null;
        }
        $json = json_decode((string)file_get_contents($path), true);
        return is_array($json) ? $json : null;
    }
}
