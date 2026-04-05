<?php
declare(strict_types=1);

namespace App\Services;

final class UsageService
{
    public function currentMonth(int $tenantId): array
    {
        return [
            'messages_used' => 0,
            'ai_used' => 0,
            'instances' => 0,
            'campaigns' => 0,
        ];
    }
}
