<?php
declare(strict_types=1);

namespace App\Integrations\Payments;

use App\Repositories\AuditLogRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\PaymentProviderLogRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\PaymentTransactionRepository;

final class PaymentService
{
    public function __construct(
        private readonly DebitoMpesaProvider $mpesa,
        private readonly DebitoEmolaProvider $emola,
        private readonly PaymentRepository $payments,
        private readonly PaymentTransactionRepository $transactions,
        private readonly PaymentProviderLogRepository $providerLogs,
        private readonly InvoiceRepository $invoices,
        private readonly AuditLogRepository $audit,
    ) {
    }

    public function checkout(int $tenantId, int $invoiceId, string $method, string $msisdn, ?string $internalNotes = null): array
    {
        $invoice = $this->invoices->findById($invoiceId);
        if (!$invoice || (int) $invoice['tenant_id'] !== $tenantId) {
            throw new \RuntimeException('Invoice inválida.');
        }

        $provider = $this->resolveProvider($method);
        $providerMethod = strtolower($method) === 'emola' ? 'emola' : 'mpesa';
        $walletId = $providerMethod === 'emola'
            ? trim((string) env('DEBITO_WALLET_ID_EMOLA', ''))
            : trim((string) env('DEBITO_WALLET_ID_MPESA', ''));

        $request = [
            'msisdn' => $msisdn,
            'amount' => (float) $invoice['amount_total'],
            'reference_description' => 'Pagamento plano ' . ($invoice['plan_name'] ?? 'MozConecta') . ' / ' . $invoice['invoice_no'],
            'internal_notes' => $internalNotes,
        ];

        $paymentId = $this->payments->createPending(
            $tenantId,
            $invoiceId,
            $provider->providerName(),
            $providerMethod,
            $walletId,
            (float) $invoice['amount_total'],
            $msisdn,
            $request
        );

        try {
            $response = $provider->createCharge($request);
            $status = strtolower((string) ($response['status'] ?? 'pending'));

            $this->payments->attachProviderData($paymentId, [
                'provider_reference' => $response['provider_reference'] ?? null,
                'debito_reference' => $response['debito_reference'] ?? null,
                'external_transaction_id' => $response['transaction_id'] ?? null,
                'provider_response_code' => $response['provider_response_code'] ?? null,
                'response_payload' => $response['response_payload'] ?? $response,
                'payment_status' => $status,
                'raw_provider_status' => $response['raw_provider_status'] ?? null,
            ]);

            $this->transactions->create([
                'payment_id' => $paymentId,
                'provider_name' => $provider->providerName(),
                'provider_reference' => $response['provider_reference'] ?? null,
                'debito_reference' => $response['debito_reference'] ?? null,
                'external_transaction_id' => $response['transaction_id'] ?? null,
                'event_type' => 'checkout_create',
                'request_payload' => $request,
                'response_payload' => $response['response_payload'] ?? $response,
                'payment_status' => $status,
                'failure_reason' => $response['failure_reason'] ?? null,
            ]);

            $this->providerLogs->log([
                'tenant_id' => $tenantId,
                'payment_id' => $paymentId,
                'provider_name' => $provider->providerName(),
                'endpoint' => $response['_endpoint'] ?? 'checkout',
                'http_method' => $response['_http_method'] ?? 'POST',
                'request_headers' => $response['_request_headers'] ?? [],
                'request_payload' => $response['_request_payload'] ?? $request,
                'response_payload' => $response['response_payload'] ?? $response,
                'response_status_code' => $response['_http_status'] ?? null,
                'latency_ms' => $response['latency_ms'] ?? null,
                'success' => true,
            ]);

            $this->audit->add($tenantId, isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null, 'billing.checkout.created', 'payments', $paymentId, [
                'provider' => $provider->providerName(),
                'provider_method' => $providerMethod,
                'status' => $status,
                'invoice_id' => $invoiceId,
            ]);

            return [
                'payment_id' => $paymentId,
                'debito_reference' => $response['debito_reference'] ?? null,
                'status' => $status,
            ];
        } catch (\Throwable $e) {
            $this->providerLogs->log([
                'tenant_id' => $tenantId,
                'payment_id' => $paymentId,
                'provider_name' => $provider->providerName(),
                'endpoint' => 'checkout',
                'http_method' => 'POST',
                'request_payload' => $request,
                'success' => false,
                'error_message' => $e->getMessage(),
            ]);

            $this->payments->updateStatus($paymentId, 'failed', $e->getMessage());
            throw $e;
        }
    }

    public function manualStatusCheck(int $tenantId, int $paymentId, PaymentStatusPollingService $polling): ?array
    {
        $payment = $this->payments->findById($paymentId);
        if (!$payment || (int) $payment['tenant_id'] !== $tenantId) {
            return null;
        }

        return $polling->pollOne($tenantId, $payment);
    }

    private function resolveProvider(string $method): PaymentProviderInterface
    {
        return strtolower($method) === 'emola' ? $this->emola : $this->mpesa;
    }
}
