<?php
declare(strict_types=1);

namespace App\Integrations\WhatsApp;

use App\Repositories\WhatsAppInstanceEventRepository;
use App\Repositories\WhatsAppInstanceRepository;

final class MessageOutboundDispatcher
{
    public function __construct(
        private readonly ProviderManager $providers,
        private readonly WhatsAppInstanceRepository $instances,
        private readonly WhatsAppInstanceEventRepository $events,
    ) {
    }

    public function dispatch(int $tenantId, int $instanceId, array $payload): array
    {
        $instance = $this->instances->findById($instanceId);
        if (!$instance) {
            throw new \RuntimeException('Instância não encontrada.');
        }

        $provider = $this->providers->for($instance['provider_name']);
        $response = $provider->sendMessage((string)$instance['provider_instance_id'], $payload);
        $this->events->log($tenantId, $instanceId, 'message_outbound', 'sent', $response, null);
        return $response;
    }
}
