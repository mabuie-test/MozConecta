<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\NotificationRepository;

final class NotificationService
{
    public function __construct(private readonly NotificationRepository $notifications)
    {
    }

    public function push(int $tenantId, string $type, string $title, string $body, ?int $userId = null, array $channels = ['in_app']): int
    {
        return $this->notifications->create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'channels_json' => $channels,
        ]);
    }

    public function unread(int $tenantId): array
    {
        return $this->notifications->unreadByTenant($tenantId);
    }

    public function markRead(int $tenantId, int $notificationId): void
    {
        $this->notifications->markRead($tenantId, $notificationId);
    }
}
