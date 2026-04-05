<?php
declare(strict_types=1);

namespace App\Repositories;

final class TaskRepository extends BaseRepository
{
    public function create(array $data): int
    {
        $this->execute('INSERT INTO tasks (tenant_id,contact_id,conversation_id,title,description,assigned_user_id,status,due_at,metadata_json,created_at,updated_at) VALUES (:tenant_id,:contact_id,:conversation_id,:title,:description,:assigned_user_id,:status,:due_at,:metadata_json,NOW(),NOW())', [
            'tenant_id' => $data['tenant_id'],
            'contact_id' => $data['contact_id'] ?? null,
            'conversation_id' => $data['conversation_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'assigned_user_id' => $data['assigned_user_id'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'due_at' => $data['due_at'] ?? null,
            'metadata_json' => isset($data['metadata_json']) ? json_encode($data['metadata_json']) : null,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function listByTenant(int $tenantId, string $bucket = 'all'): array
    {
        $sql = 'SELECT t.*, c.display_name AS contact_name, u.first_name AS assigned_first_name, u.last_name AS assigned_last_name
                FROM tasks t
                LEFT JOIN contacts c ON c.id = t.contact_id
                LEFT JOIN users u ON u.id = t.assigned_user_id
                WHERE t.tenant_id=:tenant_id AND t.deleted_at IS NULL';
        $params = ['tenant_id' => $tenantId];

        if ($bucket === 'pending') {
            $sql .= ' AND t.status IN (\'pending\',\'in_progress\')';
        } elseif ($bucket === 'overdue') {
            $sql .= ' AND t.status = \'overdue\'';
        }

        $sql .= ' ORDER BY COALESCE(t.due_at, t.created_at) ASC LIMIT 300';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $tenantId, int $taskId): ?array
    {
        return $this->fetchOne('SELECT * FROM tasks WHERE tenant_id=:tenant_id AND id=:id AND deleted_at IS NULL LIMIT 1', [
            'tenant_id' => $tenantId,
            'id' => $taskId,
        ]);
    }

    public function update(int $tenantId, int $taskId, array $data): void
    {
        $this->execute('UPDATE tasks SET title=:title, description=:description, assigned_user_id=:assigned_user_id, due_at=:due_at, status=:status, updated_at=NOW() WHERE tenant_id=:tenant_id AND id=:id', [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'assigned_user_id' => $data['assigned_user_id'] ?? null,
            'due_at' => $data['due_at'] ?? null,
            'status' => $data['status'],
            'tenant_id' => $tenantId,
            'id' => $taskId,
        ]);
    }

    public function updateStatus(int $tenantId, int $taskId, string $status): void
    {
        $done = $status === 'done' ? now() : null;
        $cancelled = $status === 'cancelled' ? now() : null;
        $this->execute('UPDATE tasks SET status=:status, completed_at=:completed_at, cancelled_at=:cancelled_at, updated_at=NOW() WHERE tenant_id=:tenant_id AND id=:id', [
            'status' => $status,
            'completed_at' => $done,
            'cancelled_at' => $cancelled,
            'tenant_id' => $tenantId,
            'id' => $taskId,
        ]);
    }

    public function markOverdue(int $tenantId): int
    {
        $stmt = $this->pdo->prepare('UPDATE tasks SET status=\'overdue\', updated_at=NOW() WHERE tenant_id=:tenant_id AND status IN (\'pending\',\'in_progress\') AND due_at IS NOT NULL AND due_at < NOW()');
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->rowCount();
    }
}
