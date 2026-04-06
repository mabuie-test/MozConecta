<?php
declare(strict_types=1);

namespace App\Integrations\WhatsApp;

use App\Support\Logger;

final class GenericApiProvider implements WhatsAppProviderInterface
{
    public function __construct(private readonly Logger $logger)
    {
    }

    public function providerName(): string
    {
        return (string) env('WHATSAPP_PROVIDER_DEFAULT', 'generic_api');
    }

    public function createInstance(array $payload): array
    {
        return $this->request('POST', '/instances', $payload);
    }

    public function startPairing(string $providerInstanceId, string $pairingMode = 'qr'): array
    {
        return $this->request('POST', '/instances/' . $providerInstanceId . '/pair', ['pairing_mode' => $pairingMode]);
    }

    public function getInstanceStatus(string $providerInstanceId): array
    {
        return $this->request('GET', '/instances/' . $providerInstanceId . '/status');
    }

    public function reconnect(string $providerInstanceId): array
    {
        return $this->request('POST', '/instances/' . $providerInstanceId . '/reconnect');
    }

    public function disconnect(string $providerInstanceId): array
    {
        return $this->request('POST', '/instances/' . $providerInstanceId . '/disconnect');
    }

    public function deleteInstance(string $providerInstanceId): array
    {
        return $this->request('DELETE', '/instances/' . $providerInstanceId);
    }

    public function sendMessage(string $providerInstanceId, array $payload): array
    {
        return $this->request('POST', '/instances/' . $providerInstanceId . '/messages', $payload);
    }

    private function request(string $method, string $endpoint, array $payload = []): array
    {
        $baseUrl = rtrim((string)env('WHATSAPP_API_BASE_URL', ''), '/');
        if ($baseUrl === '') {
            throw new \RuntimeException('WHATSAPP_API_BASE_URL não configurado.');
        }

        $url = $baseUrl . $endpoint;
        $ch = curl_init($url);
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . (string)env('WHATSAPP_API_KEY', ''),
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'], true)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $raw = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error !== '') {
            $this->logger->error('WhatsApp provider error', ['endpoint' => $endpoint, 'error' => $error]);
            throw new \RuntimeException('Erro de comunicação com provider WhatsApp.');
        }

        $response = json_decode((string)$raw, true);
        $data = is_array($response) ? $response : ['raw' => $raw];
        $data['_http_status'] = $status;

        if ($status >= 400) {
            throw new \RuntimeException('Provider WhatsApp retornou erro HTTP ' . $status);
        }

        return $data;
    }
}
