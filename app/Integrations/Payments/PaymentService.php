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
        if (!$invoice || (int)$invoice['tenant_id'] !== $tenantId) {
            throw new \RuntimeException('Invoice inválida.');
        }

        $provider = $this->resolveProvider($method);
        $paymentId = $this->payments->createPending($tenantId, $invoiceId, $provider->providerName(), (float)$invoice['amount_total'], $msisdn);

        $request = [
            'msisdn' => $msisdn,
            'amount' => (float)$invoice['amount_total'],
            'reference_description' => 'Pagamento plano ' . ($invoice['plan_name'] ?? 'MozConecta') . ' / ' . $invoice['invoice_no'],
            'internal_notes' => $internalNotes,
        ];

        try {
            $response = $provider->createCharge($request);
            $debitoReference = (string)($response['debito_reference'] ?? $response['reference'] ?? $response['transaction_reference'] ?? '');
            $externalId = (string)($response['transaction_id'] ?? $response['external_transaction_id'] ?? '');
            $status = strtolower((string)($response['status'] ?? 'pending'));

            $this->payments->attachProviderData($paymentId, [
                'provider_reference' => $debitoReference,
                'debito_reference' => $debitoReference,
                'external_transaction_id' => $externalId,
                'response_payload' => $response,
                'payment_status' => $status,
            ]);

            $this->transactions->create([
                'payment_id' => $paymentId,
                'provider_name' => $provider->providerName(),
                'provider_reference' => $debitoReference,
                'debito_reference' => $debitoReference,
                'external_transaction_id' => $externalId,
                'event_type' => 'checkout_create',
                'request_payload' => $request,
                'response_payload' => $response,
                'payment_status' => $status,
            ]);

            $this->providerLogs->log([
                'tenant_id' => $tenantId,
                'payment_id' => $paymentId,
                'provider_name' => $provider->providerName(),
                'endpoint' => $method === 'emola' ? '/api/v1/wallets/{wallet_id}/c2b/emola' : '/api/v1/wallets/{wallet_id}/c2b/mpesa',
                'method' => 'POST',
                'request_payload' => $request,
                'response_payload' => $response,
                'response_status' => $response['_http_status'] ?? null,
                'success' => true,
            ]);

            $this->audit->add($tenantId, isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null, 'billing.checkout.created', 'payments', $paymentId, [
                'provider' => $provider->providerName(),
                'status' => $status,
                'invoice_id' => $invoiceId,
            ]);

            return ['payment_id' => $paymentId, 'debito_reference' => $debitoReference, 'status' => $status];
        } catch (\Throwable $e) {
            $this->providerLogs->log([
                'tenant_id' => $tenantId,
                'payment_id' => $paymentId,
                'provider_name' => $provider->providerName(),
                'endpoint' => 'checkout',
                'method' => 'POST',
                'request_payload' => $request,
                'success' => false,
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function resolveProvider(string $method): PaymentProviderInterface
    {
        return strtolower($method) === 'emola' ? $this->emola : $this->mpesa;
    }
}
