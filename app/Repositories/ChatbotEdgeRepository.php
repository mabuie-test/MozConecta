<?php
declare(strict_types=1);

namespace App\Repositories;

final class ChatbotEdgeRepository extends BaseRepository
{
    public function create(array $data): int
    {
        $this->execute('INSERT INTO chatbot_edges (tenant_id,flow_id,from_node_id,to_node_id,condition_type,condition_value,priority,created_at,updated_at) VALUES (:tenant_id,:flow_id,:from_node_id,:to_node_id,:condition_type,:condition_value,:priority,NOW(),NOW())', [
            'tenant_id' => $data['tenant_id'],
            'flow_id' => $data['flow_id'],
            'from_node_id' => $data['from_node_id'],
            'to_node_id' => $data['to_node_id'],
            'condition_type' => $data['condition_type'] ?? 'always',
            'condition_value' => $data['condition_value'] ?? null,
            'priority' => $data['priority'] ?? 100,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function outgoing(int $flowId, int $fromNodeId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM chatbot_edges WHERE flow_id=:flow_id AND from_node_id=:from_node_id ORDER BY priority ASC, id ASC');
        $stmt->execute(['flow_id' => $flowId, 'from_node_id' => $fromNodeId]);
        return $stmt->fetchAll();
    }
}
