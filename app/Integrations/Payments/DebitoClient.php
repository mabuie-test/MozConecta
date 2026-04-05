<?php
declare(strict_types=1);

namespace App\Integrations\Payments;

use App\Support\Logger;

final class DebitoClient
{
    public function __construct(
        private readonly Logger $logger,
        private readonly DebitoAuthService $auth,
        private readonly bool $useAuth = true,
    ) {
    }

    public function request(string $method, string $endpoint, array $payload = [], bool $withAuth = true): array
    {
        $baseUrl = rtrim((string)env('DEBITO_BASE_URL', ''), '/');
        if ($baseUrl === '') {
            throw new \RuntimeException('DEBITO_BASE_URL não configurado.');
        }

        $url = $baseUrl . $endpoint;
        $ch = curl_init($url);
        $timeout = (int)env('DEBITO_TIMEOUT', 20);

        $headers = ['Content-Type: application/json', 'Accept: application/json'];
        if ($withAuth && $this->useAuth) {
            $headers[] = 'Authorization: Bearer ' . $this->auth->getToken();
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
        ]);

        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'], true)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $raw = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $decoded = json_decode((string)$raw, true);
        $response = is_array($decoded) ? $decoded : ['raw' => $raw];

        if ($error !== '') {
            $this->logger->error('DebitoClient curl error', ['endpoint' => $endpoint, 'error' => $error]);
            throw new \RuntimeException('Erro de comunicação com gateway Débito.');
        }

        if ($status >= 400) {
            $this->logger->error('DebitoClient HTTP error', ['endpoint' => $endpoint, 'status' => $status, 'response' => $response]);
            throw new \RuntimeException('Gateway Débito retornou erro HTTP ' . $status);
        }

        $response['_http_status'] = $status;
        return $response;
    }
}
