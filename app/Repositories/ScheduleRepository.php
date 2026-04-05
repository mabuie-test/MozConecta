<?php
declare(strict_types=1);

namespace App\Repositories;

final class ScheduleRepository extends BaseRepository
{
    public function create(array $data): int
    {
        $this->execute('INSERT INTO schedules (tenant_id,flow_id,node_id,conversation_id,contact_id,task_id,type,status,run_at,payload_json,created_at,updated_at) VALUES (:tenant_id,:flow_id,:node_id,:conversation_id,:contact_id,:task_id,:type,\'pending\',:run_at,:payload_json,NOW(),NOW())', [
            'tenant_id' => $data['tenant_id'],
            'flow_id' => $data['flow_id'] ?? null,
            'node_id' => $data['node_id'] ?? null,
            'conversation_id' => $data['conversation_id'] ?? null,
            'contact_id' => $data['contact_id'] ?? null,
            'task_id' => $data['task_id'] ?? null,
            'type' => $data['type'],
            'run_at' => $data['run_at'],
            'payload_json' => isset($data['payload_json']) ? json_encode($data['payload_json']) : null,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function due(int $tenantId, int $limit = 100): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM schedules WHERE tenant_id=:tenant_id AND status=\'pending\' AND run_at <= NOW() ORDER BY run_at ASC LIMIT :lim');
        $stmt->bindValue(':tenant_id', $tenantId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function markProcessed(int $id): void
    {
        $this->execute('UPDATE schedules SET status=\'processed\', processed_at=NOW(), updated_at=NOW() WHERE id=:id', ['id' => $id]);
    }

    public function latestPendingForContactFlow(int $tenantId, int $contactId, int $flowId): ?array
    {
        return $this->fetchOne('SELECT * FROM schedules WHERE tenant_id=:tenant_id AND contact_id=:contact_id AND flow_id=:flow_id AND status=\'pending\' ORDER BY id DESC LIMIT 1', [
            'tenant_id' => $tenantId,
            'contact_id' => $contactId,
            'flow_id' => $flowId,
        ]);
    }
}
