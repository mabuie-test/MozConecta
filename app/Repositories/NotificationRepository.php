<?php
declare(strict_types=1);

namespace App\Repositories;

final class NotificationRepository extends BaseRepository
{
    public function create(array $data): int
    {
        $this->execute('INSERT INTO notifications (tenant_id,user_id,type,title,body,channels_json,read_at,created_at) VALUES (:tenant_id,:user_id,:type,:title,:body,:channels_json,NULL,NOW())', [
            'tenant_id' => $data['tenant_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'type' => $data['type'],
            'title' => $data['title'],
            'body' => $data['body'],
            'channels_json' => json_encode($data['channels_json'] ?? ['in_app']),
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function unreadByTenant(int $tenantId, int $limit = 30): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM notifications WHERE tenant_id=:tenant_id AND read_at IS NULL ORDER BY id DESC LIMIT :lim');
        $stmt->bindValue(':tenant_id', $tenantId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function markRead(int $tenantId, int $id): void
    {
        $this->execute('UPDATE notifications SET read_at=NOW() WHERE tenant_id=:tenant_id AND id=:id', ['tenant_id' => $tenantId, 'id' => $id]);
    }
}
