<?php
declare(strict_types=1);

namespace App\Repositories;

final class InternetPackageRepository extends BaseRepository
{
    public function create(array $data): int
    {
        $this->execute('INSERT INTO internet_packages (tenant_id,name,description,price,validity_days,sales_message,is_active,created_at,updated_at) VALUES (:tenant_id,:name,:description,:price,:validity_days,:sales_message,:is_active,NOW(),NOW())', [
            'tenant_id' => $data['tenant_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'validity_days' => $data['validity_days'] ?? 30,
            'sales_message' => $data['sales_message'] ?? null,
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function listByTenant(int $tenantId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM internet_packages WHERE tenant_id=:tenant_id AND deleted_at IS NULL ORDER BY id DESC');
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }

    public function findById(int $tenantId, int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM internet_packages WHERE tenant_id=:tenant_id AND id=:id AND deleted_at IS NULL LIMIT 1', ['tenant_id' => $tenantId, 'id' => $id]);
    }
}
