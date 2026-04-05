<?php
declare(strict_types=1);

namespace App\Repositories;

final class InvoiceRepository extends BaseRepository
{
    public function create(int $tenantId, int $subscriptionId, int $planId, float $amount, string $currency = 'MZN'): int
    {
        $invoiceNo = 'INV-' . date('YmdHis') . '-' . random_int(1000, 9999);
        $this->execute('INSERT INTO invoices (tenant_id,subscription_id,plan_id,invoice_no,amount_subtotal,amount_total,currency,status,due_at,created_at,updated_at) VALUES (:tenant_id,:subscription_id,:plan_id,:invoice_no,:amount,:amount,:currency,\'pending\',DATE_ADD(NOW(), INTERVAL 1 DAY),NOW(),NOW())', [
            'tenant_id' => $tenantId,
            'subscription_id' => $subscriptionId,
            'plan_id' => $planId,
            'invoice_no' => $invoiceNo,
            'amount' => $amount,
            'currency' => $currency,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT i.*, p.name as plan_name FROM invoices i LEFT JOIN plans p ON p.id=i.plan_id WHERE i.id=:id AND i.deleted_at IS NULL LIMIT 1', ['id' => $id]);
    }

    public function listByTenant(int $tenantId): array
    {
        $stmt = $this->pdo->prepare('SELECT i.*, p.name as plan_name FROM invoices i LEFT JOIN plans p ON p.id=i.plan_id WHERE i.tenant_id=:tenant_id AND i.deleted_at IS NULL ORDER BY i.id DESC');
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }

    public function markPaid(int $invoiceId): void
    {
        $this->execute('UPDATE invoices SET status=\'paid\', payment_status=\'success\', paid_at=NOW(), status_checked_at=NOW(), updated_at=NOW() WHERE id=:id', ['id' => $invoiceId]);
    }

    public function markFailed(int $invoiceId): void
    {
        $this->execute('UPDATE invoices SET payment_status=\'failed\', status_checked_at=NOW(), updated_at=NOW() WHERE id=:id', ['id' => $invoiceId]);
    }
}
