<?php
declare(strict_types=1);

namespace App\Repositories;

final class WhatsAppPairingSessionRepository extends BaseRepository
{
    public function create(array $data): int
    {
        $this->execute('INSERT INTO whatsapp_pairing_sessions (tenant_id,instance_id,provider_reference,status,qr_code,qr_expires_at,pairing_payload,last_error,created_at,updated_at) VALUES (:tenant_id,:instance_id,:provider_reference,:status,:qr_code,:qr_expires_at,:pairing_payload,:last_error,NOW(),NOW())', [
            'tenant_id' => $data['tenant_id'],
            'instance_id' => $data['instance_id'],
            'provider_reference' => $data['provider_reference'],
            'status' => $data['status'],
            'qr_code' => $data['qr_code'],
            'qr_expires_at' => $data['qr_expires_at'],
            'pairing_payload' => json_encode($data['pairing_payload'] ?? []),
            'last_error' => $data['last_error'],
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function listByInstance(int $instanceId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM whatsapp_pairing_sessions WHERE instance_id=:instance_id ORDER BY id DESC');
        $stmt->execute(['instance_id' => $instanceId]);
        return $stmt->fetchAll();
    }
}
