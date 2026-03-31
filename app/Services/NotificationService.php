<?php
declare(strict_types=1);

namespace App\Services;

final class NotificationService
{
    public function push(int $tenantId, string $type, string $title): void
    {
        // persistir e entregar notificação
    }
}
