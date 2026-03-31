<?php
declare(strict_types=1);

namespace App\Services;

final class WhatsAppInstanceService
{
    public function create(array $payload): array
    {
        return ['status' => 'created'] + $payload;
    }
}
