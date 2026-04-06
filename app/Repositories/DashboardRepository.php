<?php
declare(strict_types=1);

namespace App\Repositories;

final class DashboardRepository extends BaseRepository
{
    public function tenantCounts(int $tenantId): array
    {
        $q = fn(string $sql) => (int)$this->pdo->query($sql)->fetchColumn();
        return [
            'contacts' => $q("SELECT COUNT(*) FROM contacts WHERE tenant_id = {$tenantId}"),
            'conversations' => $q("SELECT COUNT(*) FROM conversations WHERE tenant_id = {$tenantId}"),
            'tasks_pending' => $q("SELECT COUNT(*) FROM tasks WHERE tenant_id = {$tenantId} AND status IN ('pending','in_progress')"),
            'campaigns' => $q("SELECT COUNT(*) FROM campaigns WHERE tenant_id = {$tenantId}"),
        ];
    }

    public function globalStats(): array
    {
        return [
            'tenants' => (int)$this->pdo->query('SELECT COUNT(*) FROM tenants')->fetchColumn(),
            'active_subscriptions' => (int)$this->pdo->query("SELECT COUNT(*) FROM subscriptions WHERE status IN ('active','trial_active')")->fetchColumn(),
            'instances' => (int)$this->pdo->query('SELECT COUNT(*) FROM whatsapp_instances')->fetchColumn(),
        ];
    }
}
