<?php
declare(strict_types=1);

namespace App\Integrations\WhatsApp;

use App\Repositories\WhatsAppInstanceEventRepository;
use App\Repositories\WhatsAppInstanceRepository;

final class SessionSyncService
{
    public function __construct(
        private readonly ProviderManager $providers,
        private readonly WhatsAppInstanceRepository $instances,
        private readonly WhatsAppInstanceEventRepository $events,
    ) {
    }

    public function syncTenant(int $tenantId): void
    {
        $instances = $this->instances->listByTenant($tenantId);
        foreach ($instances as $instance) {
            if (empty($instance['provider_instance_id'])) {
                continue;
            }

            $provider = $this->providers->for($instance['provider_name']);
            try {
                $status = $provider->getInstanceStatus((string)$instance['provider_instance_id']);
                $newStatus = $status['status'] ?? 'unknown';
                $this->instances->applyStatusSync((int)$instance['id'], $newStatus, $status);
                $this->events->log($tenantId, (int)$instance['id'], 'session_sync', $newStatus, $status, null);
            } catch (\Throwable $e) {
                $this->instances->setError((int)$instance['id'], $e->getMessage());
                $this->events->log($tenantId, (int)$instance['id'], 'session_sync_failed', 'error', [], $e->getMessage());
            }
        }
    }
}
