<?php
declare(strict_types=1);

namespace App\Integrations\Payments;

use App\Repositories\AuditLogRepository;
use App\Repositories\PaymentProviderLogRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\SubscriptionRepository;
use App\Services\NotificationService;

final class PaymentStatusPollingService
{
    public function __construct(
        private readonly DebitoMpesaProvider $mpesa,
        private readonly DebitoEmolaProvider $emola,
        private readonly PaymentRepository $payments,
        private readonly PaymentProviderLogRepository $providerLogs,
        private readonly SubscriptionService $subscriptions,
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly AuditLogRepository $audit,
        private readonly NotificationService $notifications,
    ) {
    }

    public function pollPending(int $tenantId): void
    {
        if (!filter_var((string)env('DEBITO_STATUS_POLLING_ENABLED', 'true'), FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $pending = $this->payments->findPendingByTenant($tenantId);
        foreach ($pending as $payment) {
            if (empty($payment['debito_reference'])) {
                continue;
            }

            $provider = str_contains((string)$payment['provider'], 'emola') ? $this->emola : $this->mpesa;
            $response = $provider->checkStatus((string)$payment['debito_reference']);
            $status = strtolower((string)($response['status'] ?? 'pending'));

            $this->providerLogs->log([
                'tenant_id' => $tenantId,
                'payment_id' => (int)$payment['id'],
                'provider_name' => $provider->providerName(),
                'endpoint' => '/api/v1/transactions/{debito_reference}/status',
                'method' => 'GET',
                'response_payload' => $response,
                'response_status' => $response['_http_status'] ?? null,
                'success' => true,
            ]);

            if (in_array($status, ['success', 'paid', 'completed'], true)) {
                $subscription = $this->subscriptionRepository->latestByTenant($tenantId);
                $invoiceId = (int)$payment['invoice_id'];
                $this->subscriptions->activateFromPayment($tenantId, $invoiceId, (int)$payment['id']);
                $this->audit->add($tenantId, null, 'billing.payment.confirmed', 'payments', (int)$payment['id'], ['status' => $status]);
                $this->notifications->push($tenantId, 'payment_confirmed', 'Pagamento confirmado', 'Pagamento confirmado para invoice #' . $invoiceId . '.');
            } elseif (in_array($status, ['failed', 'cancelled', 'error'], true)) {
                $this->subscriptions->failPayment((int)$payment['invoice_id'], (int)$payment['id'], $status);
                $this->audit->add($tenantId, null, 'billing.payment.failed', 'payments', (int)$payment['id'], ['status' => $status]);
                $this->notifications->push($tenantId, 'payment_failed', 'Pagamento falhou', 'Falha no pagamento da invoice #' . (int)$payment['invoice_id'] . '.');
            }
        }
    }

    public function pollOne(int $tenantId, array $payment): array
    {
        $provider = str_contains((string)$payment['provider'], 'emola') ? $this->emola : $this->mpesa;
        $response = $provider->checkStatus((string)$payment['debito_reference']);
        return $response;
    }
}
