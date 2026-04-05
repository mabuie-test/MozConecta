<?php
declare(strict_types=1);

namespace App\Repositories;

final class RoleRepository extends BaseRepository
{
    public function findTenantRoleByCode(string $code): ?array
    {
        return $this->fetchOne('SELECT * FROM roles WHERE code=:code AND scope=\'tenant\' LIMIT 1', ['code' => $code]);
    }
}
