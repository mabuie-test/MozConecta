<?php
declare(strict_types=1);

namespace App\Integrations\WhatsApp;

use App\Repositories\WhatsAppInstanceEventRepository;
use App\Repositories\WhatsAppInstanceRepository;
use App\Services\NotificationService;

final class WhatsAppInstanceService
{
    public function __construct(
        private readonly ProviderManager $providers,
        private readonly WhatsAppInstanceRepository $instances,
        private readonly WhatsAppInstanceEventRepository $events,
        private readonly NotificationService $notifications,
    ) {
    }

    public function listByTenant(int $tenantId): array
    {
        return $this->instances->listByTenant($tenantId);
    }

    public function create(int $tenantId, array $payload): array
    {
        $connectionMethod = 'linked_devices_unofficial';
        $pairingMode = (string) ($payload['pairing_mode'] ?? 'qr');
        if (!in_array($pairingMode, ['qr', 'code'], true)) {
            $pairingMode = 'qr';
        }

        $provider = $this->providers->for($payload['provider_name'] ?? null);
        $response = $provider->createInstance([
            'name' => $payload['name'],
            'phone_number' => $payload['phone_number'],
            'webhook_secret' => $payload['webhook_secret'],
        ]);

        $metadata = array_merge($response, [
            'connection_method' => $connectionMethod,
            'official_channel' => false,
            'risk_notice' => 'Linked Devices/WhatsApp Web via QR/pairing é não oficial e pode causar banimento/instabilidade em SaaS comercial.',
        ]);

        $id = $this->instances->create([
            'tenant_id' => $tenantId,
            'name' => $payload['name'],
            'phone_number' => $payload['phone_number'],
            'provider_name' => $provider->providerName(),
            'provider_instance_id' => $response['instance_id'] ?? null,
            'status' => $response['status'] ?? 'created',
            'pairing_mode' => $pairingMode,
            'webhook_secret' => $payload['webhook_secret'],
            'metadata_json' => $metadata,
        ]);

        $this->events->log($tenantId, $id, 'instance_created', $response['status'] ?? 'created', $metadata, null);

        $this->notifications->push(
            $tenantId,
            'instance_risk_notice',
            'Método principal ativo: Linked Devices',
            'Conexão por Linked Devices/WhatsApp Web (não oficial) ativa com risco de banimento/instabilidade.'
        );

        return $this->instances->findById($id) ?? [];
    }

    public function update(int $tenantId, int $id, array $payload): void
    {
        $payload['connection_method'] = 'linked_devices_unofficial';
        $this->instances->update($tenantId, $id, $payload);
        $this->events->log($tenantId, $id, 'instance_updated', 'updated', $payload, null);
    }

    public function disconnect(int $tenantId, int $id): void
    {
        $instance = $this->instances->findById($id);
        if (!$instance) {
            return;
        }
        $provider = $this->providers->for($instance['provider_name']);
        $response = $provider->disconnect((string)$instance['provider_instance_id']);
        $this->instances->setStatus($id, 'disconnected', null, 'NOW()');
        $this->events->log($tenantId, $id, 'instance_disconnected', 'disconnected', $response, null);
        $this->notifications->push($tenantId, 'instance_disconnected', 'Instância desconectada', 'A instância #' . $id . ' foi desconectada.');
    }

    public function reconnect(int $tenantId, int $id): void
    {
        $instance = $this->instances->findById($id);
        if (!$instance) {
            return;
        }
        $provider = $this->providers->for($instance['provider_name']);
        $response = $provider->reconnect((string)$instance['provider_instance_id']);
        $this->instances->setStatus($id, 'reconnecting');
        $this->events->log($tenantId, $id, 'instance_reconnect', 'reconnecting', $response, null);
    }

    public function delete(int $tenantId, int $id): void
    {
        $instance = $this->instances->findById($id);
        if ($instance && !empty($instance['provider_instance_id'])) {
            $provider = $this->providers->for($instance['provider_name']);
            $provider->deleteInstance((string)$instance['provider_instance_id']);
        }
        $this->instances->softDelete($tenantId, $id);
        $this->events->log($tenantId, $id, 'instance_deleted', 'deleted', [], null);
    }

    public function find(int $id): ?array
    {
        return $this->instances->findById($id);
    }

    private function normalizeConnectionMethod(string $method): string
    {
        return 'linked_devices_unofficial';
    }
}
