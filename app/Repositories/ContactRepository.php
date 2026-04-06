<?php
declare(strict_types=1);

namespace App\Repositories;

final class ContactRepository extends BaseRepository
{
    public function list(int $tenantId, array $filters = []): array
    {
        $sql = 'SELECT c.*, fs.name AS stage_name, u.first_name AS assigned_first_name, u.last_name AS assigned_last_name, ls.score AS lead_score
                FROM contacts c
                LEFT JOIN funnel_stages fs ON fs.id = c.funnel_stage_id
                LEFT JOIN users u ON u.id = c.assigned_user_id
                LEFT JOIN lead_scores ls ON ls.contact_id = c.id
                WHERE c.tenant_id = :tenant_id AND c.deleted_at IS NULL';
        $params = ['tenant_id' => $tenantId];

        if (!empty($filters['search'])) {
            $sql .= ' AND (c.display_name LIKE :search OR c.phone LIKE :search OR c.email LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['stage_id'])) {
            $sql .= ' AND c.funnel_stage_id = :stage_id';
            $params['stage_id'] = (int)$filters['stage_id'];
        }
        if (!empty($filters['assigned_user_id'])) {
            $sql .= ' AND c.assigned_user_id = :assigned_user_id';
            $params['assigned_user_id'] = (int)$filters['assigned_user_id'];
        }

        $sql .= ' ORDER BY c.updated_at DESC LIMIT 300';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }


    public function findByPhone(int $tenantId, string $phone): ?array
    {
        return $this->fetchOne('SELECT * FROM contacts WHERE tenant_id=:tenant_id AND phone=:phone AND deleted_at IS NULL LIMIT 1', [
            'tenant_id' => $tenantId,
            'phone' => $phone,
        ]);
    }

    public function create(array $data): int
    {
        $this->execute('INSERT INTO contacts (tenant_id,first_name,last_name,display_name,phone,email,lead_origin,funnel_stage_id,assigned_user_id,priority,potential_value,notes,last_interaction_at,created_at,updated_at) VALUES (:tenant_id,:first_name,:last_name,:display_name,:phone,:email,:lead_origin,:funnel_stage_id,:assigned_user_id,:priority,:potential_value,:notes,:last_interaction_at,NOW(),NOW())', [
            'tenant_id' => $data['tenant_id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'display_name' => $data['display_name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'lead_origin' => $data['lead_origin'],
            'funnel_stage_id' => $data['funnel_stage_id'],
            'assigned_user_id' => $data['assigned_user_id'],
            'priority' => $data['priority'],
            'potential_value' => $data['potential_value'],
            'notes' => $data['notes'],
            'last_interaction_at' => $data['last_interaction_at'] ?? null,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function findById(int $tenantId, int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM contacts WHERE tenant_id=:tenant_id AND id=:id AND deleted_at IS NULL LIMIT 1', [
            'tenant_id' => $tenantId,
            'id' => $id,
        ]);
    }

    public function update(int $tenantId, int $id, array $data): void
    {
        $this->execute('UPDATE contacts SET display_name=:display_name, phone=:phone, email=:email, lead_origin=:lead_origin, funnel_stage_id=:funnel_stage_id, assigned_user_id=:assigned_user_id, priority=:priority, potential_value=:potential_value, notes=:notes, last_interaction_at=:last_interaction_at, updated_at=NOW() WHERE tenant_id=:tenant_id AND id=:id', [
            'display_name' => $data['display_name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'lead_origin' => $data['lead_origin'],
            'funnel_stage_id' => $data['funnel_stage_id'],
            'assigned_user_id' => $data['assigned_user_id'],
            'priority' => $data['priority'],
            'potential_value' => $data['potential_value'],
            'notes' => $data['notes'],
            'last_interaction_at' => $data['last_interaction_at'] ?? null,
            'tenant_id' => $tenantId,
            'id' => $id,
        ]);
    }

    public function moveStage(int $tenantId, int $contactId, int $stageId): void
    {
        $this->execute('UPDATE contacts SET funnel_stage_id=:stage_id, updated_at=NOW() WHERE tenant_id=:tenant_id AND id=:contact_id', [
            'stage_id' => $stageId,
            'tenant_id' => $tenantId,
            'contact_id' => $contactId,
        ]);
    }

    public function touchInteraction(int $tenantId, int $contactId): void
    {
        $this->execute('UPDATE contacts SET last_interaction_at=NOW(), updated_at=NOW() WHERE tenant_id=:tenant_id AND id=:contact_id', [
            'tenant_id' => $tenantId,
            'contact_id' => $contactId,
        ]);
    }
}
