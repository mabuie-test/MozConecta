<?php
declare(strict_types=1);

namespace App\Repositories;

final class SubscriptionRepository extends BaseRepository
{
    public function createTrial(int $tenantId, int $planId, int $statusId): void
    {
        $this->execute('INSERT INTO subscriptions (tenant_id,plan_id,status_id,starts_at,trial_starts_at,trial_ends_at,current_period_starts_at,current_period_ends_at,created_at,updated_at) VALUES (:tenant_id,:plan_id,:status_id,NOW(),NOW(),DATE_ADD(NOW(), INTERVAL 24 HOUR),NOW(),DATE_ADD(NOW(), INTERVAL 24 HOUR),NOW(),NOW())', [
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'status_id' => $statusId,
        ]);
    }

    public function latestByTenant(int $tenantId): ?array
    {
        return $this->fetchOne('SELECT s.*, ss.code AS status_code, p.name AS plan_name, p.ai_limit, p.message_limit, p.instance_limit, p.user_limit, p.feature_flags_json FROM subscriptions s JOIN subscription_statuses ss ON ss.id=s.status_id JOIN plans p ON p.id=s.plan_id WHERE s.tenant_id=:tenant_id AND s.deleted_at IS NULL ORDER BY s.id DESC LIMIT 1', ['tenant_id' => $tenantId]);
    }



    public function summaryByTenant(int $tenantId): array
    {
        $row = $this->latestByTenant($tenantId);
        return $row ?? [
            'plan_name' => null,
            'status_code' => 'trial_expired',
            'trial_starts_at' => null,
            'trial_ends_at' => null,
            'current_period_starts_at' => null,
            'current_period_ends_at' => null,
            'ai_limit' => null,
            'message_limit' => null,
        ];
    }

    public function setStatus(int $subscriptionId, int $statusId): void
    {
        $this->execute('UPDATE subscriptions SET status_id=:status_id, current_period_starts_at=NOW(), current_period_ends_at=DATE_ADD(NOW(), INTERVAL 30 DAY), updated_at=NOW() WHERE id=:id', [
            'status_id' => $statusId,
            'id' => $subscriptionId,
        ]);
    }

    public function listInvoicesByTenant(int $tenantId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM invoices WHERE tenant_id=:tenant_id AND deleted_at IS NULL ORDER BY id DESC');
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }
}
