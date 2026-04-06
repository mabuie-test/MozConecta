<?php
declare(strict_types=1);

namespace App\Integrations\Payments;

use App\Repositories\PaymentProviderLogRepository;
use App\Repositories\PaymentRepository;

final class WebhookPaymentService
{
    public function __construct(
        private readonly PaymentProviderLogRepository $logs,
        private readonly PaymentRepository $payments,
        private readonly PaymentStatusPollingService $polling,
    ) {
    }

    public function process(array $payload, ?int $tenantId = null): array
    {
        $debitoReference = (string) ($payload['debito_reference'] ?? $payload['reference'] ?? '');
        $payment = $debitoReference !== '' ? $this->payments->findByDebitoReference($debitoReference) : null;

        $this->logs->log([
            'tenant_id' => $tenantId ?? ($payment ? (int) $payment['tenant_id'] : null),
            'payment_id' => $payment['id'] ?? null,
            'provider_name' => 'debito',
            'endpoint' => '/webhooks/debito',
            'http_method' => 'POST',
            'request_payload' => $payload,
            'success' => true,
        ]);

        if (!$payment) {
            return ['processed' => false, 'reason' => 'payment_not_found'];
        }

        $this->payments->saveCallback((int) $payment['id'], $payload);

        // O callback não substitui polling: usamos o mesmo pipeline de atualização de estado.
        $this->polling->pollOne((int) $payment['tenant_id'], $payment);

        return ['processed' => true, 'payment_id' => (int) $payment['id']];
    }
}
