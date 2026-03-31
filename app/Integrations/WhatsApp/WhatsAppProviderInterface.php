<?php
declare(strict_types=1);

namespace App\Integrations\WhatsApp;

interface WhatsAppProviderInterface
{
    public function createInstance(array $payload): array;
    public function startPairing(string $instanceId): array;
    public function sendMessage(string $instanceId, array $message): array;
}
