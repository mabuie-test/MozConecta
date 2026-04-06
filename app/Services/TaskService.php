<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\AuditLogRepository;
use App\Repositories\TaskRepository;

final class TaskService
{
    public function __construct(
        private readonly TaskRepository $tasks,
        private readonly AuditLogRepository $auditLogs,
        private readonly NotificationService $notifications,
    ) {
    }

    public function list(int $tenantId, string $bucket = 'all'): array
    {
        $overdue = $this->tasks->markOverdue($tenantId);
        if ($overdue > 0) {
            $this->notifications->push($tenantId, "task_overdue", "Tarefas vencidas", $overdue . " tarefa(s) marcadas como vencidas.");
        }
        return $this->tasks->listByTenant($tenantId, $bucket);
    }

    public function create(int $tenantId, int $actorUserId, array $payload): int
    {
        $id = $this->tasks->create([
            'tenant_id' => $tenantId,
            'contact_id' => (int)($payload['contact_id'] ?? 0) ?: null,
            'conversation_id' => (int)($payload['conversation_id'] ?? 0) ?: null,
            'title' => trim((string)$payload['title']),
            'description' => (string)($payload['description'] ?? ''),
            'assigned_user_id' => (int)($payload['assigned_user_id'] ?? 0) ?: null,
            'status' => (string)($payload['status'] ?? 'pending'),
            'due_at' => (string)($payload['due_at'] ?? '') ?: null,
            'metadata_json' => $payload['metadata_json'] ?? null,
        ]);

        $this->auditLogs->add($tenantId, $actorUserId, 'task_created', 'task', $id, ['title' => $payload['title']]);
        return $id;
    }

    public function update(int $tenantId, int $actorUserId, int $taskId, array $payload): void
    {
        $this->tasks->update($tenantId, $taskId, [
            'title' => trim((string)$payload['title']),
            'description' => (string)($payload['description'] ?? ''),
            'assigned_user_id' => (int)($payload['assigned_user_id'] ?? 0) ?: null,
            'due_at' => (string)($payload['due_at'] ?? '') ?: null,
            'status' => (string)($payload['status'] ?? 'pending'),
        ]);

        $this->auditLogs->add($tenantId, $actorUserId, 'task_updated', 'task', $taskId, ['status' => $payload['status'] ?? 'pending']);
    }

    public function changeStatus(int $tenantId, int $actorUserId, int $taskId, string $status): void
    {
        $allowed = ['pending', 'in_progress', 'done', 'cancelled', 'overdue'];
        if (!in_array($status, $allowed, true)) {
            return;
        }

        $this->tasks->updateStatus($tenantId, $taskId, $status);
        $this->auditLogs->add($tenantId, $actorUserId, 'task_status_changed', 'task', $taskId, ['status' => $status]);
    }
}
