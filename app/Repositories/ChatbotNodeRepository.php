<?php
declare(strict_types=1);

namespace App\Repositories;

final class ChatbotNodeRepository extends BaseRepository
{
    public function create(array $data): int
    {
        $this->execute('INSERT INTO chatbot_nodes (tenant_id,flow_id,node_key,type,config_json,position_x,position_y,is_start,created_at,updated_at) VALUES (:tenant_id,:flow_id,:node_key,:type,:config_json,:position_x,:position_y,:is_start,NOW(),NOW())', [
            'tenant_id' => $data['tenant_id'],
            'flow_id' => $data['flow_id'],
            'node_key' => $data['node_key'],
            'type' => $data['type'],
            'config_json' => json_encode($data['config_json'] ?? []),
            'position_x' => $data['position_x'] ?? null,
            'position_y' => $data['position_y'] ?? null,
            'is_start' => $data['is_start'] ? 1 : 0,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function byFlow(int $tenantId, int $flowId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM chatbot_nodes WHERE tenant_id=:tenant_id AND flow_id=:flow_id ORDER BY id ASC');
        $stmt->execute(['tenant_id' => $tenantId, 'flow_id' => $flowId]);
        return $stmt->fetchAll();
    }

    public function findStart(int $tenantId, int $flowId): ?array
    {
        return $this->fetchOne('SELECT * FROM chatbot_nodes WHERE tenant_id=:tenant_id AND flow_id=:flow_id AND is_start=1 LIMIT 1', [
            'tenant_id' => $tenantId,
            'flow_id' => $flowId,
        ]);
    }

    public function findById(int $tenantId, int $nodeId): ?array
    {
        return $this->fetchOne('SELECT * FROM chatbot_nodes WHERE tenant_id=:tenant_id AND id=:id LIMIT 1', [
            'tenant_id' => $tenantId,
            'id' => $nodeId,
        ]);
    }
}
