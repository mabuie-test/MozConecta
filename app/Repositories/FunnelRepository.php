<?php
declare(strict_types=1);

namespace App\Repositories;

final class FunnelRepository extends BaseRepository
{
    public function findDefaultByTenant(int $tenantId): ?array
    {
        return $this->fetchOne('SELECT * FROM funnels WHERE tenant_id=:tenant_id AND is_default=1 LIMIT 1', ['tenant_id' => $tenantId]);
    }

    public function createDefault(int $tenantId): int
    {
        $this->execute('INSERT INTO funnels (tenant_id,name,is_default,created_at,updated_at) VALUES (:tenant_id,:name,1,NOW(),NOW())', [
            'tenant_id' => $tenantId,
            'name' => 'Pipeline Comercial',
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function addStage(int $tenantId, int $funnelId, string $name, string $slug, int $position, bool $won = false): void
    {
        $this->execute('INSERT INTO funnel_stages (tenant_id,funnel_id,name,slug,position,is_won_stage,is_lost_stage,created_at,updated_at) VALUES (:tenant_id,:funnel_id,:name,:slug,:position,:is_won,:is_lost,NOW(),NOW())', [
            'tenant_id' => $tenantId,
            'funnel_id' => $funnelId,
            'name' => $name,
            'slug' => $slug,
            'position' => $position,
            'is_won' => $won ? 1 : 0,
            'is_lost' => 0,
        ]);
    }

    public function listStages(int $tenantId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM funnel_stages WHERE tenant_id=:tenant_id ORDER BY position ASC');
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }

    public function findStage(int $tenantId, int $stageId): ?array
    {
        return $this->fetchOne('SELECT * FROM funnel_stages WHERE tenant_id=:tenant_id AND id=:id LIMIT 1', [
            'tenant_id' => $tenantId,
            'id' => $stageId,
        ]);
    }
}
