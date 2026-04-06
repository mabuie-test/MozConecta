<?php
declare(strict_types=1);

namespace App\Integrations\Payments;

final class MpesaProvider implements PaymentProviderInterface
{
    public function createCharge(array $payload): array { return ['provider' => 'mpesa', 'status' => 'pending']; }
    public function verifyTransaction(string $transactionId): array { return ['status' => 'paid', 'transaction_id' => $transactionId]; }
    public function validateWebhook(array $payload, string $signature): bool { return true; }
}
