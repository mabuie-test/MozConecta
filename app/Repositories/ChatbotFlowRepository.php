<?php
declare(strict_types=1);

namespace App\Repositories;

final class ChatbotFlowRepository extends BaseRepository
{
    public function listByTenant(int $tenantId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM chatbot_flows WHERE tenant_id=:tenant_id AND deleted_at IS NULL ORDER BY id DESC');
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $this->execute('INSERT INTO chatbot_flows (tenant_id,name,trigger_type,trigger_value,fallback_message,allow_reentry,allow_remarketing,is_active,created_by_user_id,created_at,updated_at) VALUES (:tenant_id,:name,:trigger_type,:trigger_value,:fallback_message,:allow_reentry,:allow_remarketing,:is_active,:created_by_user_id,NOW(),NOW())', [
            'tenant_id' => $data['tenant_id'],
            'name' => $data['name'],
            'trigger_type' => $data['trigger_type'],
            'trigger_value' => $data['trigger_value'] ?? null,
            'fallback_message' => $data['fallback_message'] ?? null,
            'allow_reentry' => $data['allow_reentry'] ? 1 : 0,
            'allow_remarketing' => $data['allow_remarketing'] ? 1 : 0,
            'is_active' => $data['is_active'] ? 1 : 0,
            'created_by_user_id' => $data['created_by_user_id'] ?? null,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function findById(int $tenantId, int $flowId): ?array
    {
        return $this->fetchOne('SELECT * FROM chatbot_flows WHERE tenant_id=:tenant_id AND id=:id AND deleted_at IS NULL LIMIT 1', [
            'tenant_id' => $tenantId,
            'id' => $flowId,
        ]);
    }

    public function activeForInbound(int $tenantId, string $input): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM chatbot_flows WHERE tenant_id=:tenant_id AND is_active=1 AND deleted_at IS NULL ORDER BY id DESC');
        $stmt->execute(['tenant_id' => $tenantId]);
        $flows = $stmt->fetchAll();

        foreach ($flows as $flow) {
            if ($flow['trigger_type'] === 'all_inbound') {
                return $flow;
            }
            if ($flow['trigger_type'] === 'keyword' && $flow['trigger_value']) {
                $keywords = array_filter(array_map('trim', explode(',', (string)$flow['trigger_value'])));
                foreach ($keywords as $keyword) {
                    if ($keyword !== '' && str_contains(mb_strtolower($input), mb_strtolower($keyword))) {
                        return $flow;
                    }
                }
            }
        }

        return null;
    }

    public function setActive(int $tenantId, int $flowId, bool $active): void
    {
        $this->execute('UPDATE chatbot_flows SET is_active=:active, updated_at=NOW() WHERE tenant_id=:tenant_id AND id=:id', [
            'active' => $active ? 1 : 0,
            'tenant_id' => $tenantId,
            'id' => $flowId,
        ]);
    }

    public function hasCompletedForContact(int $tenantId, int $flowId, int $contactId): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM chatbot_execution_logs WHERE tenant_id=:tenant_id AND flow_id=:flow_id AND contact_id=:contact_id AND event_type=\'flow_completed\'');
        $stmt->execute(['tenant_id' => $tenantId, 'flow_id' => $flowId, 'contact_id' => $contactId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
