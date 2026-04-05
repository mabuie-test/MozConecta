<?php
declare(strict_types=1);

namespace App\Integrations\WhatsApp;

use App\Repositories\ContactRepository;
use App\Repositories\ConversationMessageRepository;
use App\Repositories\ConversationRepository;
use App\Repositories\WhatsAppInstanceEventRepository;
use App\Services\AutomationEngineService;
use App\Services\HybridDecisionService;

final class MessageInboundProcessor
{
    public function __construct(
        private readonly WhatsAppInstanceEventRepository $events,
        private readonly ContactRepository $contacts,
        private readonly ConversationRepository $conversations,
        private readonly ConversationMessageRepository $messages,
        private readonly AutomationEngineService $automation,
        private readonly HybridDecisionService $hybrid,
    ) {
    }

    public function process(int $tenantId, int $instanceId, array $payload): void
    {
        $phone = trim((string)($payload['from'] ?? $payload['phone'] ?? ''));
        $body = trim((string)($payload['body'] ?? $payload['message'] ?? ''));

        if ($phone !== '' && $body !== '') {
            $contact = $this->contacts->findByPhone($tenantId, $phone);
            if (!$contact) {
                $contactId = $this->contacts->create([
                    'tenant_id' => $tenantId,
                    'first_name' => null,
                    'last_name' => null,
                    'display_name' => $phone,
                    'phone' => $phone,
                    'email' => null,
                    'lead_origin' => 'whatsapp_inbound',
                    'funnel_stage_id' => null,
                    'assigned_user_id' => null,
                    'priority' => 'medium',
                    'potential_value' => null,
                    'notes' => null,
                    'last_interaction_at' => now(),
                ]);
                $contact = $this->contacts->findById($tenantId, $contactId);
            }

            if ($contact) {
                $conversationId = $this->conversations->findOrCreateOpen($tenantId, (int)$contact['id']);
                $this->messages->add([
                    'tenant_id' => $tenantId,
                    'conversation_id' => $conversationId,
                    'contact_id' => (int)$contact['id'],
                    'direction' => 'inbound',
                    'message_type' => 'text',
                    'body' => $body,
                    'payload_json' => $payload,
                    'external_message_id' => $payload['message_id'] ?? null,
                    'sent_by_user_id' => null,
                ]);
                $this->conversations->touchLastMessage($tenantId, $conversationId);
                $this->contacts->touchInteraction($tenantId, (int)$contact['id']);

                $flowResult = $this->automation->processInbound($tenantId, $conversationId, (int)$contact['id'], $body) ?? [];
                $this->hybrid->handleInbound($tenantId, $conversationId, (int)$contact['id'], $body, $flowResult);
            }
        }

        $this->events->log($tenantId, $instanceId, 'message_inbound', 'received', $payload, null);
    }
}
