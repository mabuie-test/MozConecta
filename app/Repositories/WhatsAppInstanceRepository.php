<?php
declare(strict_types=1);

namespace App\Repositories;

final class WhatsAppInstanceRepository extends BaseRepository
{
    public function create(array $data): int
    {
        $this->execute('INSERT INTO whatsapp_instances (tenant_id,name,phone_number,provider_name,provider_instance_id,status,pairing_mode,webhook_secret,metadata_json,created_at,updated_at) VALUES (:tenant_id,:name,:phone_number,:provider_name,:provider_instance_id,:status,:pairing_mode,:webhook_secret,:metadata_json,NOW(),NOW())', [
            'tenant_id' => $data['tenant_id'],
            'name' => $data['name'],
            'phone_number' => $data['phone_number'],
            'provider_name' => $data['provider_name'],
            'provider_instance_id' => $data['provider_instance_id'],
            'status' => $data['status'],
            'pairing_mode' => $data['pairing_mode'],
            'webhook_secret' => $data['webhook_secret'],
            'metadata_json' => json_encode($data['metadata_json'] ?? []),
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function listByTenant(int $tenantId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM whatsapp_instances WHERE tenant_id=:tenant_id AND deleted_at IS NULL ORDER BY id DESC');
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM whatsapp_instances WHERE id=:id AND deleted_at IS NULL LIMIT 1', ['id' => $id]);
    }

    public function findByWebhookSecret(string $secret): ?array
    {
        return $this->fetchOne('SELECT * FROM whatsapp_instances WHERE webhook_secret=:secret AND deleted_at IS NULL LIMIT 1', ['secret' => $secret]);
    }

    public function update(int $tenantId, int $id, array $payload): void
    {
        $this->execute('UPDATE whatsapp_instances SET name=:name, phone_number=:phone_number, pairing_mode=:pairing_mode, updated_at=NOW() WHERE id=:id AND tenant_id=:tenant_id', [
            'name' => $payload['name'],
            'phone_number' => $payload['phone_number'],
            'pairing_mode' => $payload['pairing_mode'],
            'id' => $id,
            'tenant_id' => $tenantId,
        ]);
    }

    public function setPairingData(int $id, array $data): void
    {
        $this->execute('UPDATE whatsapp_instances SET status=:status, qr_code=:qr_code, qr_expires_at=:qr_expires_at, updated_at=NOW() WHERE id=:id', [
            'status' => $data['status'],
            'qr_code' => $data['qr_code'],
            'qr_expires_at' => $data['qr_expires_at'],
            'id' => $id,
        ]);
    }

    public function setStatus(int $id, string $status, ?string $lastError = null, ?string $disconnectedAtSql = null): void
    {
        $disconnectedAt = $disconnectedAtSql === 'NOW()' ? now() : null;
        $this->execute('UPDATE whatsapp_instances SET status=:status, last_error=:last_error, disconnected_at=:disconnected_at, updated_at=NOW() WHERE id=:id', [
            'status' => $status,
            'last_error' => $lastError,
            'disconnected_at' => $disconnectedAt,
            'id' => $id,
        ]);
    }

    public function applyStatusSync(int $id, string $status, array $metadata): void
    {
        $connectedAt = in_array($status, ['connected'], true) ? now() : null;
        $disconnectedAt = in_array($status, ['disconnected', 'error', 'blocked'], true) ? now() : null;
        $this->execute('UPDATE whatsapp_instances SET status=:status, metadata_json=:metadata_json, last_seen_at=NOW(), connected_at=COALESCE(:connected_at, connected_at), disconnected_at=COALESCE(:disconnected_at, disconnected_at), updated_at=NOW() WHERE id=:id', [
            'status' => $status,
            'metadata_json' => json_encode($metadata),
            'connected_at' => $connectedAt,
            'disconnected_at' => $disconnectedAt,
            'id' => $id,
        ]);
    }

    public function setError(int $id, string $error): void
    {
        $this->execute('UPDATE whatsapp_instances SET status=\'error\', last_error=:error, updated_at=NOW() WHERE id=:id', [
            'error' => $error,
            'id' => $id,
        ]);
    }

    public function softDelete(int $tenantId, int $id): void
    {
        $this->execute('UPDATE whatsapp_instances SET deleted_at=NOW(), updated_at=NOW() WHERE id=:id AND tenant_id=:tenant_id', [
            'id' => $id,
            'tenant_id' => $tenantId,
        ]);
    }
}
