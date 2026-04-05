<?php
declare(strict_types=1);

namespace App\Repositories;

final class UserRepository extends BaseRepository
{
    public function findByEmail(string $email): ?array
    {
        return $this->fetchOne('SELECT * FROM users WHERE email = :email AND deleted_at IS NULL LIMIT 1', ['email' => $email]);
    }

    public function getTenantMemberships(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT tu.*, r.code as role_code, r.name as role_name FROM tenant_users tu JOIN roles r ON r.id=tu.role_id WHERE tu.user_id=:user_id AND tu.deleted_at IS NULL');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
