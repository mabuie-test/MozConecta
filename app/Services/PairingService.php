<?php
declare(strict_types=1);

namespace App\Services;

final class PairingService
{
    public function start(int $instanceId): array
    {
        return ['instance_id' => $instanceId, 'status' => 'qr_ready'];
    }
}
