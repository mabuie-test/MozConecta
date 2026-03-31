<?php
declare(strict_types=1);

namespace App\Repositories;

final class SubscriptionRepository extends BaseRepository
{
    public function createTrial(int $tenantId, int $planId): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO subscriptions (tenant_id,plan_id,status,trial_starts_at,trial_ends_at,current_period_starts_at,current_period_ends_at,created_at,updated_at) VALUES (:tenant,:plan,:status,NOW(),DATE_ADD(NOW(), INTERVAL 24 HOUR),NOW(),DATE_ADD(NOW(), INTERVAL 24 HOUR),NOW(),NOW())');
        $stmt->execute(['tenant' => $tenantId, 'plan' => $planId, 'status' => 'trial_active']);
    }

    public function summaryByTenant(int $tenantId): array
    {
        $stmt = $this->pdo->prepare('SELECT s.*, p.name AS plan_name FROM subscriptions s JOIN plans p ON p.id=s.plan_id WHERE s.tenant_id=:tenant ORDER BY s.id DESC LIMIT 1');
        $stmt->execute(['tenant' => $tenantId]);
        return $stmt->fetch() ?: [];
    }
}
