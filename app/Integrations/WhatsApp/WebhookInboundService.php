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

    public function handle(array $payload, string $secret, string $rawBody = '', ?string $signature = null): void
    {
        $instance = $this->instances->findByWebhookSecret($secret);
        if (!$instance) {
            throw new \RuntimeException('Webhook secret inválido.');
        }

        if (filter_var((string)env('WHATSAPP_VALIDATE_SIGNATURE', 'false'), FILTER_VALIDATE_BOOLEAN)) {
            $expected = hash_hmac('sha256', $rawBody, (string)$secret);
            if (!$signature || !hash_equals($expected, $signature)) {
                throw new \RuntimeException('Assinatura de webhook inválida.');
            }
        }

        $this->inbound->process((int)$instance['tenant_id'], (int)$instance['id'], $payload);
    }
}
