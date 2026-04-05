<?php
declare(strict_types=1);

namespace App\Integrations\Payments;

use App\Support\Logger;

final class DebitoMpesaProvider implements PaymentProviderInterface
{
    public function __construct(private readonly Logger $logger, private readonly DebitoAuthService $auth)
    {
    }

    public function providerName(): string
    {
        return 'debito';
    }

    public function createCharge(array $payload): array
    {
        $walletId = trim((string) env('DEBITO_WALLET_ID_MPESA', ''));
        if ($walletId === '') {
            throw new \RuntimeException('DEBITO_WALLET_ID_MPESA não configurado.');
        }

        $body = $this->buildPayload($payload);
        $endpoint = '/api/v1/wallets/' . rawurlencode($walletId) . '/c2b/mpesa';

        $client = new DebitoClient($this->logger, $this->auth);
        $response = $client->request('POST', $endpoint, $body, true);

        return $this->normalizeResponse($response, 'mpesa', $walletId);
    }

    public function checkStatus(string $debitoReference): array
    {
        $client = new DebitoClient($this->logger, $this->auth);
        $response = $client->request('GET', '/api/v1/transactions/' . rawurlencode($debitoReference) . '/status', [], true);

        return $this->normalizeResponse($response, 'mpesa', trim((string) env('DEBITO_WALLET_ID_MPESA', '')));
    }

    private function buildPayload(array $payload): array
    {
        $msisdn = preg_replace('/\s+/', '', (string) ($payload['msisdn'] ?? ''));
        $amount = (float) ($payload['amount'] ?? 0);
        $referenceDescription = trim((string) ($payload['reference_description'] ?? ''));

        if ($msisdn === '' || $amount < 1 || $referenceDescription === '') {
            throw new \InvalidArgumentException('Dados obrigatórios inválidos para M-Pesa.');
        }

        $body = [
            'msisdn' => $msisdn,
            'amount' => $amount,
            'reference_description' => $referenceDescription,
        ];

        if (!empty($payload['internal_notes'])) {
            $body['internal_notes'] = (string) $payload['internal_notes'];
        }

        $callback = trim((string) env('DEBITO_CALLBACK_URL', ''));
        if ($callback !== '') {
            $body['callback_url'] = $callback;
        }

        return $body;
    }

    private function normalizeResponse(array $response, string $method, string $walletId): array
    {
        $status = strtolower((string) ($response['status'] ?? $response['transaction_status'] ?? 'pending'));

        return [
            'provider_name' => 'debito',
            'provider_method' => $method,
            'wallet_id_used' => $walletId,
            'debito_reference' => (string) ($response['debito_reference'] ?? $response['reference'] ?? $response['transaction_reference'] ?? ''),
            'transaction_id' => (string) ($response['transaction_id'] ?? $response['id'] ?? ''),
            'provider_reference' => (string) ($response['provider_reference'] ?? $response['reference'] ?? ''),
            'provider_response_code' => (string) ($response['code'] ?? $response['response_code'] ?? ''),
            'status' => $status,
            'raw_provider_status' => (string) ($response['raw_status'] ?? $response['status'] ?? ''),
            'response_payload' => $response,
            '_http_status' => $response['_http_status'] ?? null,
            '_endpoint' => $response['_endpoint'] ?? null,
            '_http_method' => $response['_http_method'] ?? null,
            '_request_payload' => $response['_request_payload'] ?? [],
            '_request_headers' => $response['_request_headers'] ?? [],
            'latency_ms' => $response['latency_ms'] ?? null,
            'timestamp' => $response['timestamp'] ?? null,
            'failure_reason' => $response['_error'] ?? $response['message'] ?? null,
        ];
    }
}
