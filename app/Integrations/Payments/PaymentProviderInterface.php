<?php
declare(strict_types=1);

namespace App\Integrations\Payments;

interface PaymentProviderInterface
{
    public function createCharge(array $payload): array;
    public function verifyTransaction(string $transactionId): array;
    public function validateWebhook(array $payload, string $signature): bool;
}
