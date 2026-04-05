<?php
declare(strict_types=1);

namespace App\Repositories;

final class PaymentRepository extends BaseRepository
{
    public function createPending(int $tenantId, int $invoiceId, string $provider, float $amount, string $msisdn): int
    {
        $this->execute('INSERT INTO payments (tenant_id,invoice_id,provider,provider_name,status,amount,currency,payer_phone,payment_status,created_at,updated_at) VALUES (:tenant_id,:invoice_id,:provider,:provider_name,\'pending\',:amount,\'MZN\',:phone,\'pending\',NOW(),NOW())', [
            'tenant_id' => $tenantId,
            'invoice_id' => $invoiceId,
            'provider' => $provider,
            'provider_name' => $provider,
            'amount' => $amount,
            'phone' => $msisdn,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function attachProviderData(int $paymentId, array $data): void
    {
        $this->execute('UPDATE payments SET provider_reference=:provider_reference, debito_reference=:debito_reference, external_transaction_id=:external_transaction_id, response_payload=:response_payload, payment_status=:payment_status, status_checked_at=NOW(), updated_at=NOW() WHERE id=:id', [
            'provider_reference' => $data['provider_reference'] ?? null,
            'debito_reference' => $data['debito_reference'] ?? null,
            'external_transaction_id' => $data['external_transaction_id'] ?? null,
            'response_payload' => json_encode($data['response_payload'] ?? null),
            'payment_status' => $data['payment_status'] ?? 'pending',
            'id' => $paymentId,
        ]);
    }

    public function updateStatus(int $paymentId, string $status, ?string $failureReason = null): void
    {
        $final = $status === 'success' ? 'paid' : ($status === 'failed' ? 'failed' : 'pending');
        $this->execute('UPDATE payments SET status=:status, payment_status=:payment_status, status_checked_at=NOW(), paid_at=IF(:status = \"paid\", NOW(), paid_at), failure_reason=:failure_reason, updated_at=NOW() WHERE id=:id', [
            'status' => $final,
            'payment_status' => $status,
            'failure_reason' => $failureReason,
            'id' => $paymentId,
        ]);
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
}
