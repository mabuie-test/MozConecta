<?php
declare(strict_types=1);

namespace App\Repositories;

final class UserRepository extends BaseRepository
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT u.*, tu.tenant_id, tu.role FROM users u JOIN tenant_users tu ON tu.user_id = u.id WHERE u.email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name, string $email, string $passwordHash): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (name,email,password_hash,status,created_at,updated_at) VALUES (:name,:email,:pass,:status,NOW(),NOW())');
        $stmt->execute(['name' => $name, 'email' => $email, 'pass' => $passwordHash, 'status' => 'active']);
        return (int)$this->pdo->lastInsertId();
    }
}
