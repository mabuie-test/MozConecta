<?php
declare(strict_types=1);

namespace App\Repositories;

final class CampaignRepository extends BaseRepository
{
    public function create(array $data): int
    {
        $this->execute('INSERT INTO campaigns (tenant_id,name,type,channel,message_template,segment_type,segment_value,status,batch_size,scheduled_at,metadata_json,created_at,updated_at) VALUES (:tenant_id,:name,:type,:channel,:message_template,:segment_type,:segment_value,:status,:batch_size,:scheduled_at,:metadata_json,NOW(),NOW())', [
            'tenant_id' => $data['tenant_id'],
            'name' => $data['name'],
            'type' => $data['type'],
            'channel' => 'whatsapp',
            'message_template' => $data['message_template'],
            'segment_type' => $data['segment_type'],
            'segment_value' => $data['segment_value'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'batch_size' => $data['batch_size'] ?? 50,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'metadata_json' => isset($data['metadata_json']) ? json_encode($data['metadata_json']) : null,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function listByTenant(int $tenantId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM campaigns WHERE tenant_id=:tenant_id AND deleted_at IS NULL ORDER BY id DESC');
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }

    public function findById(int $tenantId, int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM campaigns WHERE tenant_id=:tenant_id AND id=:id AND deleted_at IS NULL LIMIT 1', ['tenant_id' => $tenantId, 'id' => $id]);
    }

    public function updateStatus(int $tenantId, int $id, string $status): void
    {
        $updates = 'status=:status, updated_at=NOW()';
        if ($status === 'running') {
            $updates .= ', started_at=COALESCE(started_at,NOW())';
        }
        if ($status === 'completed') {
            $updates .= ', completed_at=NOW()';
        }
        $this->execute("UPDATE campaigns SET {$updates} WHERE tenant_id=:tenant_id AND id=:id", [
            'status' => $status,
            'tenant_id' => $tenantId,
            'id' => $id,
        ]);
    }

    public function incrementStats(int $tenantId, int $id, string $field): void
    {
        $allowed = ['sent_count', 'delivered_count', 'failed_count'];
        if (!in_array($field, $allowed, true)) {
            return;
        }
        $this->execute("UPDATE campaigns SET {$field}={$field}+1, updated_at=NOW() WHERE tenant_id=:tenant_id AND id=:id", [
            'tenant_id' => $tenantId,
            'id' => $id,
        ]);
    }

    public function setTotalRecipients(int $tenantId, int $id, int $count): void
    {
        $this->execute('UPDATE campaigns SET total_recipients=:count, updated_at=NOW() WHERE tenant_id=:tenant_id AND id=:id', [
            'count' => $count,
            'tenant_id' => $tenantId,
            'id' => $id,
        ]);
    }
}
