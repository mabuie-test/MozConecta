<?php
declare(strict_types=1);

namespace App\Repositories;

final class WhatsAppInstanceEventRepository extends BaseRepository
{
    public function log(int $tenantId, int $instanceId, string $type, ?string $status, array $payload = [], ?string $message = null): void
    {
        $this->execute('INSERT INTO whatsapp_instance_events (tenant_id,instance_id,event_type,event_status,event_payload,technical_message,created_at) VALUES (:tenant_id,:instance_id,:event_type,:event_status,:event_payload,:technical_message,NOW())', [
            'tenant_id' => $tenantId,
            'instance_id' => $instanceId,
            'event_type' => $type,
            'event_status' => $status,
            'event_payload' => json_encode($payload),
            'technical_message' => $message,
        ]);
    }

    public function listByInstance(int $instanceId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM whatsapp_instance_events WHERE instance_id=:instance_id ORDER BY id DESC LIMIT 200');
        $stmt->execute(['instance_id' => $instanceId]);
        return $stmt->fetchAll();
    }
}
