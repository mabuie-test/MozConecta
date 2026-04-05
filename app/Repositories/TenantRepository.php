<?php
declare(strict_types=1);

namespace App\Repositories;

final class TenantRepository extends BaseRepository
{
    public function findBySlug(string $slug): ?array
    {
        return $this->fetchOne('SELECT * FROM tenants WHERE slug = :slug AND deleted_at IS NULL LIMIT 1', ['slug' => $slug]);
    }

    public function forUser(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT t.*, tu.role_id, tu.status AS membership_status FROM tenant_users tu JOIN tenants t ON t.id=tu.tenant_id WHERE tu.user_id=:user_id AND tu.deleted_at IS NULL AND t.deleted_at IS NULL');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
