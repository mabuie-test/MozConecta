<?php
declare(strict_types=1);

namespace App\Repositories;

final class SubscriptionRepository extends BaseRepository
{
    public function latestByTenant(int $tenantId): ?array
    {
        return $this->fetchOne('SELECT s.*, ss.code AS status_code, p.name AS plan_name FROM subscriptions s JOIN subscription_statuses ss ON ss.id=s.status_id JOIN plans p ON p.id=s.plan_id WHERE s.tenant_id=:tenant_id AND s.deleted_at IS NULL ORDER BY s.id DESC LIMIT 1', ['tenant_id' => $tenantId]);
    }

    public function listInvoicesByTenant(int $tenantId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM invoices WHERE tenant_id=:tenant_id AND deleted_at IS NULL ORDER BY id DESC');
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }
}
