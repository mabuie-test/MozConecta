<?php
declare(strict_types=1);

namespace App\Integrations\WhatsApp;

interface WhatsAppProviderInterface
{
    public function providerName(): string;
    public function createInstance(array $payload): array;
    public function startPairing(string $providerInstanceId, string $pairingMode = 'qr'): array;
    public function getInstanceStatus(string $providerInstanceId): array;
    public function reconnect(string $providerInstanceId): array;
    public function disconnect(string $providerInstanceId): array;
    public function deleteInstance(string $providerInstanceId): array;
    public function sendMessage(string $providerInstanceId, array $payload): array;
}
