<?php
declare(strict_types=1);

namespace App\Repositories;

final class PasswordResetRepository extends BaseRepository
{
    public function create(int $userId, string $tokenHash, int $ttlMinutes = 30): void
    {
        $this->execute('INSERT INTO password_resets (user_id, token_hash, expires_at, created_at) VALUES (:user_id,:token_hash,DATE_ADD(NOW(), INTERVAL :ttl MINUTE),NOW())', [
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'ttl' => $ttlMinutes,
        ]);
    }

    public function findValidByUser(int $userId): ?array
    {
        return $this->fetchOne('SELECT * FROM password_resets WHERE user_id=:user_id AND used_at IS NULL AND expires_at > NOW() ORDER BY id DESC LIMIT 1', ['user_id' => $userId]);
    }

    public function markUsed(int $id): void
    {
        $this->execute('UPDATE password_resets SET used_at = NOW() WHERE id=:id', ['id' => $id]);
    }
}
