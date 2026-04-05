<?php
declare(strict_types=1);

namespace App\Integrations\Payments;

use App\Support\Logger;

final class DebitoEmolaProvider implements PaymentProviderInterface
{
    public function __construct(private readonly Logger $logger, private readonly DebitoAuthService $auth)
    {
    }

    public function providerName(): string
    {
        return 'debito_emola';
    }

    public function createCharge(array $payload): array
    {
        $walletId = env('DEBITO_WALLET_ID', '');
        $endpoint = '/api/v1/wallets/' . $walletId . '/c2b/emola';

        $body = [
            'msisdn' => $payload['msisdn'],
            'amount' => $payload['amount'],
            'reference_description' => $payload['reference_description'],
            'internal_notes' => $payload['internal_notes'] ?? null,
        ];

        $client = new DebitoClient($this->logger, $this->auth);
        return $client->request('POST', $endpoint, $body, true);
    }

    public function checkStatus(string $debitoReference): array
    {
        $client = new DebitoClient($this->logger, $this->auth);
        return $client->request('GET', '/api/v1/transactions/' . $debitoReference . '/status', [], true);
    }
}
