<?php
declare(strict_types=1);

namespace App\Repositories;

final class AuditLogRepository extends BaseRepository
{
    public function add(?int $tenantId, ?int $userId, string $action, string $entityType, ?int $entityId, array $newValues = []): void
    {
        $this->execute('INSERT INTO audit_logs (tenant_id,user_id,action,entity_type,entity_id,new_values_json,created_at) VALUES (:tenant_id,:user_id,:action,:entity_type,:entity_id,:new_values_json,NOW())', [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'new_values_json' => json_encode($newValues),
        ]);
    }
}
