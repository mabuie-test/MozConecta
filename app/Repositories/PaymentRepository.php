<?php
declare(strict_types=1);

namespace App\Repositories;

final class PaymentRepository extends BaseRepository
{
    public function createPending(
        int $tenantId,
        int $invoiceId,
        string $providerName,
        string $providerMethod,
        string $walletId,
        float $amount,
        string $msisdn,
        array $requestPayload
    ): int {
        $this->execute('INSERT INTO payments (tenant_id,invoice_id,provider,provider_name,provider_method,wallet_id_used,status,amount,currency,payer_phone,request_payload,payment_status,poll_attempts,created_at,updated_at) VALUES (:tenant_id,:invoice_id,:provider,:provider_name,:provider_method,:wallet_id_used,\'pending\',:amount,\'MZN\',:phone,:request_payload,\'pending\',0,NOW(),NOW())', [
            'tenant_id' => $tenantId,
            'invoice_id' => $invoiceId,
            'provider' => $providerMethod,
            'provider_name' => $providerName,
            'provider_method' => $providerMethod,
            'wallet_id_used' => $walletId,
            'amount' => $amount,
            'phone' => $msisdn,
            'request_payload' => json_encode($requestPayload, JSON_UNESCAPED_UNICODE),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function attachProviderData(int $paymentId, array $data): void
    {
        $this->execute('UPDATE payments SET provider_reference=:provider_reference, debito_reference=:debito_reference, external_transaction_id=:external_transaction_id, response_payload=:response_payload, provider_response_code=:provider_response_code, payment_status=:payment_status, raw_provider_status=:raw_provider_status, status_checked_at=NOW(), updated_at=NOW() WHERE id=:id', [
            'provider_reference' => $data['provider_reference'] ?? null,
            'debito_reference' => $data['debito_reference'] ?? null,
            'external_transaction_id' => $data['external_transaction_id'] ?? null,
            'response_payload' => json_encode($data['response_payload'] ?? null, JSON_UNESCAPED_UNICODE),
            'provider_response_code' => $data['provider_response_code'] ?? null,
            'payment_status' => $data['payment_status'] ?? 'pending',
            'raw_provider_status' => $data['raw_provider_status'] ?? null,
            'id' => $paymentId,
        ]);
    }

    public function saveCallback(int $paymentId, array $payload): void
    {
        $this->execute('UPDATE payments SET callback_payload=:callback_payload, callback_received_at=NOW(), updated_at=NOW() WHERE id=:id', [
            'callback_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'id' => $paymentId,
        ]);
    }

    public function markPolled(int $paymentId): void
    {
        $this->execute('UPDATE payments SET poll_attempts=poll_attempts+1, last_poll_at=NOW(), updated_at=NOW() WHERE id=:id', ['id' => $paymentId]);
    }

    public function updateStatus(int $paymentId, string $status, ?string $failureReason = null, ?string $rawProviderStatus = null): void
    {
        $normalized = strtolower($status);
        $final = match ($normalized) {
            'success', 'paid', 'completed' => 'paid',
            'failed', 'error' => 'failed',
            'cancelled', 'canceled' => 'cancelled',
            default => 'pending',
        };

        $this->execute('UPDATE payments SET status=:status, payment_status=:payment_status, raw_provider_status=:raw_provider_status, status_checked_at=NOW(), paid_at=IF(:status = "paid", NOW(), paid_at), failure_reason=:failure_reason, updated_at=NOW() WHERE id=:id', [
            'status' => $final,
            'payment_status' => $normalized,
            'raw_provider_status' => $rawProviderStatus,
            'failure_reason' => $failureReason,
            'id' => $paymentId,
        ]);
    }

    public function findByDebitoReference(string $debitoReference): ?array
    {
        return $this->fetchOne('SELECT * FROM payments WHERE debito_reference=:debito_reference AND deleted_at IS NULL LIMIT 1', ['debito_reference' => $debitoReference]);
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM payments WHERE id=:id AND deleted_at IS NULL LIMIT 1', ['id' => $id]);
    }

    public function findPendingByTenant(int $tenantId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM payments WHERE tenant_id=:tenant_id AND payment_status IN (\'pending\',\'processing\') AND deleted_at IS NULL ORDER BY id DESC');
        $stmt->execute(['tenant_id' => $tenantId]);

        return $stmt->fetchAll();
    }

    public function findPendingDueForPolling(int $tenantId, int $intervalSeconds): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM payments WHERE tenant_id=:tenant_id AND payment_status IN (\'pending\',\'processing\') AND deleted_at IS NULL AND (last_poll_at IS NULL OR TIMESTAMPDIFF(SECOND,last_poll_at,NOW()) >= :interval_seconds) ORDER BY id ASC');
        $stmt->execute(['tenant_id' => $tenantId, 'interval_seconds' => $intervalSeconds]);

        return $stmt->fetchAll();
    }

    public function listByTenantDetailed(int $tenantId): array
    {
        $stmt = $this->pdo->prepare('SELECT p.*, i.invoice_no, i.status AS invoice_status, i.plan_id, i.amount_total FROM payments p JOIN invoices i ON i.id=p.invoice_id WHERE p.tenant_id=:tenant_id AND p.deleted_at IS NULL ORDER BY p.id DESC');
        $stmt->execute(['tenant_id' => $tenantId]);

        return $stmt->fetchAll();
    }
}
