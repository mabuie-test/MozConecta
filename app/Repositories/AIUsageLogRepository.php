<?php
declare(strict_types=1);

namespace App\Repositories;

final class AIUsageLogRepository extends BaseRepository
{
    public function log(array $data): void
    {
        $this->execute('INSERT INTO ai_usage_logs (tenant_id,conversation_id,contact_id,provider_name,usage_type,units_used,metadata_json,created_at) VALUES (:tenant_id,:conversation_id,:contact_id,:provider_name,:usage_type,:units_used,:metadata_json,NOW())', [
            'tenant_id' => $data['tenant_id'],
            'conversation_id' => $data['conversation_id'] ?? null,
            'contact_id' => $data['contact_id'] ?? null,
            'provider_name' => $data['provider_name'],
            'usage_type' => $data['usage_type'] ?? 'message',
            'units_used' => $data['units_used'] ?? 1,
            'metadata_json' => isset($data['metadata_json']) ? json_encode($data['metadata_json']) : null,
        ]);
    }

    public function monthUnits(int $tenantId, string $usageType = 'message'): int
    {
        $stmt = $this->pdo->prepare('SELECT COALESCE(SUM(units_used),0) FROM ai_usage_logs WHERE tenant_id=:tenant_id AND usage_type=:usage_type AND created_at >= DATE_FORMAT(NOW(), "%Y-%m-01 00:00:00")');
        $stmt->execute(['tenant_id' => $tenantId, 'usage_type' => $usageType]);
        return (int)$stmt->fetchColumn();
    }
}
