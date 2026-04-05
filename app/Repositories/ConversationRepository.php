<?php
declare(strict_types=1);

namespace App\Repositories;

final class ConversationRepository extends BaseRepository
{
    public function listByTenant(int $tenantId, array $filters = []): array
    {
        $sql = 'SELECT cv.*, c.display_name AS contact_name, c.phone AS contact_phone, u.first_name AS assigned_first_name, u.last_name AS assigned_last_name
                FROM conversations cv
                INNER JOIN contacts c ON c.id = cv.contact_id
                LEFT JOIN users u ON u.id = cv.assigned_user_id
                WHERE cv.tenant_id=:tenant_id AND cv.deleted_at IS NULL';
        $params = ['tenant_id' => $tenantId];

        if (!empty($filters['status'])) {
            $sql .= ' AND cv.status=:status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['assigned_user_id'])) {
            $sql .= ' AND cv.assigned_user_id=:assigned_user_id';
            $params['assigned_user_id'] = (int)$filters['assigned_user_id'];
        }
        if (!empty($filters['search'])) {
            $sql .= ' AND (c.display_name LIKE :search OR c.phone LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= ' ORDER BY cv.last_message_at DESC, cv.updated_at DESC LIMIT 300';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $tenantId, int $id): ?array
    {
        return $this->fetchOne('SELECT cv.*, c.display_name AS contact_name, c.phone AS contact_phone FROM conversations cv INNER JOIN contacts c ON c.id=cv.contact_id WHERE cv.tenant_id=:tenant_id AND cv.id=:id AND cv.deleted_at IS NULL LIMIT 1', [
            'tenant_id' => $tenantId,
            'id' => $id,
        ]);
    }

    public function findOrCreateOpen(int $tenantId, int $contactId): int
    {
        $existing = $this->fetchOne('SELECT id FROM conversations WHERE tenant_id=:tenant_id AND contact_id=:contact_id AND status IN (\'open\',\'pending\') AND deleted_at IS NULL ORDER BY id DESC LIMIT 1', [
            'tenant_id' => $tenantId,
            'contact_id' => $contactId,
        ]);
        if ($existing) {
            return (int)$existing['id'];
        }

        $this->execute('INSERT INTO conversations (tenant_id,contact_id,status,last_message_at,created_at,updated_at) VALUES (:tenant_id,:contact_id,\'open\',NOW(),NOW(),NOW())', [
            'tenant_id' => $tenantId,
            'contact_id' => $contactId,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function changeStatus(int $tenantId, int $conversationId, string $status): void
    {
        $this->execute('UPDATE conversations SET status=:status, updated_at=NOW() WHERE tenant_id=:tenant_id AND id=:id', [
            'status' => $status,
            'tenant_id' => $tenantId,
            'id' => $conversationId,
        ]);
    }

    public function assign(int $tenantId, int $conversationId, int $userId): void
    {
        $this->execute('UPDATE conversations SET assigned_user_id=:user_id, updated_at=NOW() WHERE tenant_id=:tenant_id AND id=:id', [
            'user_id' => $userId,
            'tenant_id' => $tenantId,
            'id' => $conversationId,
        ]);
    }

    public function takeover(int $tenantId, int $conversationId, int $userId): void
    {
        $this->execute('UPDATE conversations SET takeover_by_user_id=:user_id, assigned_user_id=:user_id, status=\'open\', updated_at=NOW() WHERE tenant_id=:tenant_id AND id=:id', [
            'user_id' => $userId,
            'tenant_id' => $tenantId,
            'id' => $conversationId,
        ]);
    }

    public function appendInternalNotes(int $tenantId, int $conversationId, string $note): void
    {
        $this->execute('UPDATE conversations SET internal_notes=CONCAT(IFNULL(internal_notes,\'\'), :note), updated_at=NOW() WHERE tenant_id=:tenant_id AND id=:id', [
            'note' => "\n- " . trim($note),
            'tenant_id' => $tenantId,
            'id' => $conversationId,
        ]);
    }

    public function touchLastMessage(int $tenantId, int $conversationId): void
    {
        $this->execute('UPDATE conversations SET last_message_at=NOW(), updated_at=NOW() WHERE tenant_id=:tenant_id AND id=:id', [
            'tenant_id' => $tenantId,
            'id' => $conversationId,
        ]);
    }
}
