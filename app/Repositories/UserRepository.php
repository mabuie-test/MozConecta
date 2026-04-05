<?php
declare(strict_types=1);

namespace App\Repositories;

final class UserRepository extends BaseRepository
{
    public function findByEmail(string $email): ?array
    {
        return $this->fetchOne('SELECT * FROM users WHERE email = :email AND deleted_at IS NULL LIMIT 1', ['email' => $email]);
    }

    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO users (uuid, first_name, last_name, email, phone, password_hash, status, email_verified_at, created_at, updated_at) VALUES (UUID(), :first_name, :last_name, :email, :phone, :password_hash, :status, :email_verified_at, NOW(), NOW())',
            [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? null,
                'email' => strtolower($data['email']),
                'phone' => $data['phone'] ?? null,
                'password_hash' => $data['password_hash'],
                'status' => $data['status'] ?? 'active',
                'email_verified_at' => $data['email_verified_at'] ?? null,
            ]
        );
        return (int) $this->pdo->lastInsertId();
    }

    public function updateProfile(int $userId, string $firstName, ?string $lastName, ?string $phone): void
    {
        $this->execute('UPDATE users SET first_name=:first_name, last_name=:last_name, phone=:phone, updated_at=NOW() WHERE id=:id', [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone,
            'id' => $userId,
        ]);
    }

    public function updatePassword(int $userId, string $passwordHash): void
    {
        $this->execute('UPDATE users SET password_hash=:password_hash, failed_attempts=0, locked_until=NULL, updated_at=NOW() WHERE id=:id', [
            'password_hash' => $passwordHash,
            'id' => $userId,
        ]);
    }

    public function incrementFailedAttempts(int $userId): void
    {
        $this->execute('UPDATE users SET failed_attempts = failed_attempts + 1, updated_at = NOW() WHERE id = :id', ['id' => $userId]);
    }

    public function lockForMinutes(int $userId, int $minutes): void
    {
        $this->execute('UPDATE users SET locked_until = DATE_ADD(NOW(), INTERVAL :minutes MINUTE), updated_at = NOW() WHERE id = :id', [
            'minutes' => $minutes,
            'id' => $userId,
        ]);
    }

    public function clearFailedAttempts(int $userId): void
    {
        $this->execute('UPDATE users SET failed_attempts=0, locked_until=NULL, last_login_at=NOW(), updated_at=NOW() WHERE id=:id', ['id' => $userId]);
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM users WHERE id=:id AND deleted_at IS NULL LIMIT 1', ['id' => $id]);
    }

    public function getTenantMemberships(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT tu.*, r.code AS role_code, t.id AS tenant_id, t.name AS tenant_name FROM tenant_users tu JOIN roles r ON r.id=tu.role_id JOIN tenants t ON t.id=tu.tenant_id WHERE tu.user_id=:user_id AND tu.deleted_at IS NULL AND t.deleted_at IS NULL ORDER BY tu.id DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function emailConsumedTrial(string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users u JOIN tenant_users tu ON tu.user_id=u.id JOIN subscriptions s ON s.tenant_id=tu.tenant_id WHERE u.email=:email AND s.trial_starts_at IS NOT NULL');
        $stmt->execute(['email' => strtolower($email)]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function phoneConsumedTrial(string $phone): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users u JOIN tenant_users tu ON tu.user_id=u.id JOIN subscriptions s ON s.tenant_id=tu.tenant_id WHERE u.phone=:phone AND s.trial_starts_at IS NOT NULL');
        $stmt->execute(['phone' => $phone]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
