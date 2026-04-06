<?php
declare(strict_types=1);

namespace App\Repositories;

final class ChatbotExecutionLogRepository extends BaseRepository
{
    public function log(int $tenantId, ?int $flowId, ?int $nodeId, ?int $conversationId, ?int $contactId, string $eventType, array $payload = []): void
    {
        $this->execute('INSERT INTO chatbot_execution_logs (tenant_id,flow_id,node_id,conversation_id,contact_id,event_type,event_payload,created_at) VALUES (:tenant_id,:flow_id,:node_id,:conversation_id,:contact_id,:event_type,:event_payload,NOW())', [
            'tenant_id' => $tenantId,
            'flow_id' => $flowId,
            'node_id' => $nodeId,
            'conversation_id' => $conversationId,
            'contact_id' => $contactId,
            'event_type' => $eventType,
            'event_payload' => json_encode($payload),
        ]);
    }

    public function latestByFlow(int $tenantId, int $flowId, int $limit = 200): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM chatbot_execution_logs WHERE tenant_id=:tenant_id AND flow_id=:flow_id ORDER BY id DESC LIMIT :lim');
        $stmt->bindValue(':tenant_id', $tenantId, \PDO::PARAM_INT);
        $stmt->bindValue(':flow_id', $flowId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
