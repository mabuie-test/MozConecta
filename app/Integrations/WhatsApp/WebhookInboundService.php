<?php
declare(strict_types=1);

namespace App\Integrations\WhatsApp;

use App\Repositories\WhatsAppInstanceRepository;

final class WebhookInboundService
{
    public function __construct(
        private readonly MessageInboundProcessor $inbound,
        private readonly WhatsAppInstanceRepository $instances,
    ) {
    }

    public function handle(array $payload, string $secret): void
    {
        $instance = $this->instances->findByWebhookSecret($secret);
        if (!$instance) {
            throw new \RuntimeException('Webhook secret inválido.');
        }

        $this->inbound->process((int)$instance['tenant_id'], (int)$instance['id'], $payload);
    }
}
