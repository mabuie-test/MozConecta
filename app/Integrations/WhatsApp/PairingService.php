<?php
declare(strict_types=1);

namespace App\Integrations\WhatsApp;

use App\Repositories\WhatsAppInstanceEventRepository;
use App\Repositories\WhatsAppInstanceRepository;
use App\Repositories\WhatsAppPairingSessionRepository;

final class PairingService
{
    public function __construct(
        private readonly ProviderManager $providers,
        private readonly WhatsAppInstanceRepository $instances,
        private readonly WhatsAppPairingSessionRepository $pairings,
        private readonly WhatsAppInstanceEventRepository $events,
    ) {
    }

    public function start(int $tenantId, int $instanceId): array
    {
        $instance = $this->instances->findById($instanceId);
        if (!$instance) {
            throw new \RuntimeException('Instância não encontrada.');
        }

        $provider = $this->providers->for($instance['provider_name']);
        $response = $provider->startPairing((string)$instance['provider_instance_id'], (string)$instance['pairing_mode']);

        $this->pairings->create([
            'tenant_id' => $tenantId,
            'instance_id' => $instanceId,
            'provider_reference' => $response['reference'] ?? null,
            'status' => $response['status'] ?? 'pending',
            'qr_code' => $response['qr_code'] ?? null,
            'qr_expires_at' => $response['qr_expires_at'] ?? null,
            'pairing_payload' => $response,
            'last_error' => null,
        ]);

        $this->instances->setPairingData($instanceId, [
            'status' => $response['status'] ?? 'pending_pair',
            'qr_code' => $response['qr_code'] ?? null,
            'qr_expires_at' => $response['qr_expires_at'] ?? null,
        ]);

        $this->events->log($tenantId, $instanceId, 'pairing_started', $response['status'] ?? 'pending', $response, null);
        return $response;
    }
}
