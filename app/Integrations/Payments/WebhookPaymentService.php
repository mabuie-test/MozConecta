<?php
declare(strict_types=1);

namespace App\Integrations\Payments;

use App\Repositories\PaymentProviderLogRepository;

final class WebhookPaymentService
{
    public function __construct(private readonly PaymentProviderLogRepository $logs)
    {
    }

    public function process(array $payload, ?int $tenantId = null): void
    {
        $this->logs->log([
            'tenant_id' => $tenantId,
            'provider_name' => 'debito_webhook',
            'endpoint' => '/webhooks/debito',
            'method' => 'POST',
            'request_payload' => $payload,
            'success' => true,
        ]);

        // Fluxo de ativação fica preparado para assinatura de webhook + idempotência por referência.
    }
}
