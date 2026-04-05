<?php
declare(strict_types=1);

namespace App\Integrations\WhatsApp;

use App\Repositories\WhatsAppInstanceEventRepository;

final class MessageInboundProcessor
{
    public function __construct(private readonly WhatsAppInstanceEventRepository $events)
    {
    }

    public function process(int $tenantId, int $instanceId, array $payload): void
    {
        // FASE 5: camada pronta para ligar inbox/CRM nas próximas fases
        $this->events->log($tenantId, $instanceId, 'message_inbound', 'received', $payload, null);
    }
}
