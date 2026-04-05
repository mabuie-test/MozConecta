<?php
declare(strict_types=1);

namespace App\Repositories;

final class SubscriptionStatusRepository extends BaseRepository
{
    public function findByCode(string $code): ?array
    {
        return $this->fetchOne('SELECT * FROM subscription_statuses WHERE code=:code LIMIT 1', ['code' => $code]);
    }
}
