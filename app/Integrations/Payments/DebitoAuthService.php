<?php
declare(strict_types=1);

namespace App\Integrations\Payments;

use App\Support\Logger;

final class DebitoAuthService
{
    private const TOKEN_CACHE_FILE = '/storage/cache/debito_token.json';

    private ?array $tokenCache = null;

    public function __construct(private readonly Logger $logger)
    {
    }

    public function getToken(): string
    {
        $envToken = trim((string) env('DEBITO_TOKEN', ''));
        if ($envToken !== '') {
            return $envToken;
        }

        $cached = $this->readCachedToken();
        if ($cached && (int) ($cached['expires_at'] ?? 0) > time() + 30) {
            return (string) ($cached['token'] ?? '');
        }

        return $this->refreshTokenFromLogin();
    }

    public function invalidate(): void
    {
        $this->tokenCache = null;
        $path = base_path(self::TOKEN_CACHE_FILE);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function refreshTokenFromLogin(): string
    {
        $email = trim((string) env('DEBITO_EMAIL', ''));
        $password = trim((string) env('DEBITO_PASSWORD', ''));

        if ($email === '' || $password === '') {
            throw new \RuntimeException('DEBITO_TOKEN não definido e credenciais DEBITO_EMAIL/DEBITO_PASSWORD ausentes.');
        }

        $client = new DebitoClient($this->logger, $this, false);
        $response = $client->request('POST', '/api/v1/login', [
            'email' => $email,
            'password' => $password,
        ], false);

        $token = trim((string) ($response['data']['token'] ?? $response['token'] ?? $response['access_token'] ?? ''));
        if ($token === '') {
            $this->logger->error('Falha ao obter token Débito via login', ['response' => $response]);
            throw new \RuntimeException('Falha ao autenticar na API Débito.');
        }

        $expiresIn = (int) ($response['data']['expires_in'] ?? $response['expires_in'] ?? 3600);
        $this->cacheToken($token, time() + max(60, $expiresIn));

        return $token;
    }

    private function cacheToken(string $token, int $expiresAt): void
    {
        $path = base_path(self::TOKEN_CACHE_FILE);
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        $payload = ['token' => $token, 'expires_at' => $expiresAt];
        $this->tokenCache = $payload;
        file_put_contents($path, json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    private function readCachedToken(): ?array
    {
        if (is_array($this->tokenCache)) {
            return $this->tokenCache;
        }

        $path = base_path(self::TOKEN_CACHE_FILE);
        if (!is_file($path)) {
            return null;
        }

        $data = json_decode((string) file_get_contents($path), true);
        if (!is_array($data)) {
            return null;
        }

        $this->tokenCache = $data;

        return $data;
    }
}
