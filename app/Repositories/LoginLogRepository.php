<?php
declare(strict_types=1);

namespace App\Repositories;

final class LoginLogRepository extends BaseRepository
{
    public function add(?int $userId, string $email, ?string $ip, ?string $ua, bool $success, ?string $reason = null): void
    {
        $this->execute('INSERT INTO login_logs (user_id,email,ip_address,user_agent,success,failure_reason,created_at) VALUES (:user_id,:email,:ip,:ua,:success,:reason,NOW())', [
            'user_id' => $userId,
            'email' => strtolower($email),
            'ip' => $ip,
            'ua' => $ua,
            'success' => $success ? 1 : 0,
            'reason' => $reason,
        ]);
    }
}
