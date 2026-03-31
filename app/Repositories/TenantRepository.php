<?php
declare(strict_types=1);

namespace App\Repositories;

final class TenantRepository extends BaseRepository
{
    public function create(string $name, string $slug): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO tenants (name,slug,status,trial_consumed,created_at,updated_at) VALUES (:name,:slug,:status,0,NOW(),NOW())');
        $stmt->execute(['name' => $name, 'slug' => $slug, 'status' => 'active']);
        return (int)$this->pdo->lastInsertId();
    }

    public function attachUser(int $tenantId, int $userId, string $role): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO tenant_users (tenant_id,user_id,role,created_at) VALUES (:tenant,:user,:role,NOW())');
        $stmt->execute(['tenant' => $tenantId, 'user' => $userId, 'role' => $role]);
    }
}
