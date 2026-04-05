<?php
declare(strict_types=1);

namespace App\Repositories;

final class TenantRepository extends BaseRepository
{
    public function create(array $data): int
    {
        $this->execute('INSERT INTO tenants (uuid, name, slug, email, phone, status, trial_consumed, created_at, updated_at) VALUES (UUID(), :name, :slug, :email, :phone, :status, :trial_consumed, NOW(), NOW())', [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'] ?? 'active',
            'trial_consumed' => $data['trial_consumed'] ?? 1,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function attachUser(int $tenantId, int $userId, int $roleId, bool $isOwner = false): void
    {
        $this->execute('INSERT INTO tenant_users (tenant_id,user_id,role_id,is_owner,joined_at,status,created_at,updated_at) VALUES (:tenant_id,:user_id,:role_id,:is_owner,NOW(),:status,NOW(),NOW())', [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'role_id' => $roleId,
            'is_owner' => $isOwner ? 1 : 0,
            'status' => 'active',
        ]);
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->fetchOne('SELECT * FROM tenants WHERE slug = :slug AND deleted_at IS NULL LIMIT 1', ['slug' => $slug]);
    }

    public function generateUniqueSlug(string $company): string
    {
        $base = trim((string)preg_replace('/[^a-z0-9]+/i', '-', strtolower($company)), '-');
        $slug = $base;
        $counter = 1;
        while ($this->findBySlug($slug)) {
            $slug = $base . '-' . $counter;
            $counter++;
        }
        return $slug;
    }
}
