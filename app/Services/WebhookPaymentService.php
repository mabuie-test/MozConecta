<?php
declare(strict_types=1);

namespace App\Services;

final class WebhookPaymentService
{
    public function process(array $payload): array
    {
        return ['processed' => true, 'idempotent_key' => $payload['idempotency_key'] ?? null];
    }
}
