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
        $method = strtoupper($method);
        if (!in_array($method, ['GET', 'POST'], true)) {
            throw new \InvalidArgumentException('Método HTTP não suportado para Débito: ' . $method);
        }

        $baseUrl = rtrim((string) env('DEBITO_BASE_URL', 'https://my.debito.co.mz'), '/');
        if ($baseUrl === '') {
            throw new \RuntimeException('DEBITO_BASE_URL não configurado.');
        }

        $attempts = max(1, (int) env('DEBITO_RETRY_COUNT', 3));
        $timeout = max(5, (int) env('DEBITO_TIMEOUT', 30));

        $lastError = null;
        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            $startedAt = microtime(true);
            $response = $this->performRequest($baseUrl, $method, $endpoint, $payload, $withAuth, $timeout);
            $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);
            $response['latency_ms'] = $latencyMs;
            $response['timestamp'] = date('c');

            $statusCode = (int) ($response['_http_status'] ?? 0);
            $error = (string) ($response['_error'] ?? '');
            $retryable = $error !== '' || $statusCode >= 500 || $statusCode === 429;

            $this->logger->info('Débito HTTP request', [
                'endpoint' => $endpoint,
                'method' => $method,
                'status' => $statusCode,
                'latency_ms' => $latencyMs,
                'attempt' => $attempt,
                'retryable' => $retryable,
            ]);

            if ($error === '' && $statusCode > 0 && $statusCode < 400) {
                return $response;
            }

            $lastError = $response;

            if (!$retryable || $attempt === $attempts) {
                break;
            }

            usleep(150000 * $attempt);
        }

        $message = (string) ($lastError['_error'] ?? ('Gateway Débito retornou erro HTTP ' . ($lastError['_http_status'] ?? '0')));
        throw new \RuntimeException($message);
    }

    private function performRequest(
        string $baseUrl,
        string $method,
        string $endpoint,
        array $payload,
        bool $withAuth,
        int $timeout
    ): array {
        $url = $baseUrl . $endpoint;
        $headers = ['Content-Type: application/json', 'Accept: application/json'];
        if ($withAuth && $this->useAuth) {
            $headers[] = 'Authorization: Bearer ' . $this->auth->getToken();
        }

        if ($method === 'GET' && $payload !== []) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($payload);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
        }

        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $decoded = json_decode((string) $raw, true);
        $response = is_array($decoded) ? $decoded : ['raw' => (string) $raw];
        $response['_http_status'] = $status;
        $response['_endpoint'] = $endpoint;
        $response['_http_method'] = $method;
        $response['_request_payload'] = $payload;
        $response['_request_headers'] = $this->sanitizeHeaders($headers);

        if ($error !== '') {
            $response['_error'] = 'Erro de rede/timeout Débito: ' . $error;
            return $response;
        }

        if ($status >= 400) {
            $response['_error'] = (string) ($response['message'] ?? ('Erro HTTP ' . $status));
        }

        return $response;
    }

    private function sanitizeHeaders(array $headers): array
    {
        return array_map(static function (string $header): string {
            if (str_starts_with(strtolower($header), 'authorization: bearer ')) {
                return 'Authorization: Bearer ***';
            }
            return $header;
        }, $headers);
    }
}
