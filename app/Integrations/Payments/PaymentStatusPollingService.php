<?php
declare(strict_types=1);

namespace App\Integrations\Payments;

use App\Repositories\AuditLogRepository;
use App\Repositories\PaymentProviderLogRepository;
use App\Repositories\PaymentRepository;
use App\Services\NotificationService;

final class PaymentStatusPollingService
{
    public function __construct(
        private readonly DebitoMpesaProvider $mpesa,
        private readonly DebitoEmolaProvider $emola,
        private readonly PaymentRepository $payments,
        private readonly PaymentProviderLogRepository $providerLogs,
        private readonly SubscriptionService $subscriptions,
        private readonly AuditLogRepository $audit,
        private readonly NotificationService $notifications,
    ) {
    }

    public function pollPending(int $tenantId): void
    {
        if (!filter_var((string) env('DEBITO_STATUS_POLLING_ENABLED', 'true'), FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $intervalSeconds = max(15, (int) env('DEBITO_STATUS_POLLING_INTERVAL', 60));
        $pending = $this->payments->findPendingDueForPolling($tenantId, $intervalSeconds);

        foreach ($pending as $payment) {
            $this->pollOne($tenantId, $payment);
        }
    }

    public function pollOne(int $tenantId, array $payment): array
    {
        if ((int) ($payment['tenant_id'] ?? 0) !== $tenantId || empty($payment['debito_reference'])) {
            return ['status' => 'skipped'];
        }

        $provider = ((string) ($payment['provider_method'] ?? $payment['provider'] ?? 'mpesa')) === 'emola'
            ? $this->emola
            : $this->mpesa;

        $this->payments->markPolled((int) $payment['id']);

        try {
            $response = $provider->checkStatus((string) $payment['debito_reference']);
            $status = strtolower((string) ($response['status'] ?? 'pending'));

            $this->providerLogs->log([
                'tenant_id' => $tenantId,
                'payment_id' => (int) $payment['id'],
                'provider_name' => $provider->providerName(),
                'endpoint' => $response['_endpoint'] ?? '/api/v1/transactions/{debito_reference}/status',
                'http_method' => $response['_http_method'] ?? 'GET',
                'request_headers' => $response['_request_headers'] ?? [],
                'request_payload' => $response['_request_payload'] ?? [],
                'response_payload' => $response['response_payload'] ?? $response,
                'response_status_code' => $response['_http_status'] ?? null,
                'latency_ms' => $response['latency_ms'] ?? null,
                'success' => true,
            ]);

            $this->payments->updateStatus((int) $payment['id'], $status, $response['failure_reason'] ?? null, $response['raw_provider_status'] ?? null);
            $this->handleBusinessOutcome($tenantId, $payment, $status, $response);

            return ['status' => $status, 'debito_reference' => $payment['debito_reference']];
        } catch (\Throwable $e) {
            $this->providerLogs->log([
                'tenant_id' => $tenantId,
                'payment_id' => (int) $payment['id'],
                'provider_name' => $provider->providerName(),
                'endpoint' => '/api/v1/transactions/{debito_reference}/status',
                'http_method' => 'GET',
                'request_payload' => ['debito_reference' => $payment['debito_reference']],
                'success' => false,
                'error_message' => $e->getMessage(),
            ]);

            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function handleBusinessOutcome(int $tenantId, array $payment, string $status, array $response): void
    {
        if (in_array($status, ['success', 'paid', 'completed'], true)) {
            if (in_array((string) ($payment['status'] ?? ''), ['paid'], true)) {
                return; // idempotência
            }

            $invoiceId = (int) $payment['invoice_id'];
            $this->subscriptions->activateFromPayment($tenantId, $invoiceId, (int) $payment['id']);
            $this->audit->add($tenantId, null, 'billing.payment.confirmed', 'payments', (int) $payment['id'], [
                'status' => $status,
                'debito_reference' => $payment['debito_reference'],
            ]);
            $this->notifications->push($tenantId, 'payment_confirmed', 'Pagamento confirmado', 'Pagamento confirmado para invoice #' . $invoiceId . '.');

            return;
        }

        if (in_array($status, ['failed', 'cancelled', 'canceled', 'error'], true)) {
            $this->subscriptions->failPayment((int) $payment['invoice_id'], (int) $payment['id'], (string) ($response['failure_reason'] ?? $status));
            $this->audit->add($tenantId, null, 'billing.payment.failed', 'payments', (int) $payment['id'], [
                'status' => $status,
                'debito_reference' => $payment['debito_reference'],
            ]);
            $this->notifications->push($tenantId, 'payment_failed', 'Pagamento falhou', 'Falha no pagamento da invoice #' . (int) $payment['invoice_id'] . '.');
        }
    }
}
