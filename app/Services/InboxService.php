<?php
declare(strict_types=1);

namespace App\Services;

use App\Integrations\WhatsApp\MessageOutboundDispatcher;
use App\Repositories\AuditLogRepository;
use App\Repositories\ContactRepository;
use App\Repositories\ConversationMessageRepository;
use App\Repositories\ConversationRepository;
use App\Repositories\WhatsAppInstanceRepository;

final class InboxService
{
    public function __construct(
        private readonly ConversationRepository $conversations,
        private readonly ConversationMessageRepository $messages,
        private readonly ContactRepository $contacts,
        private readonly MessageOutboundDispatcher $dispatcher,
        private readonly WhatsAppInstanceRepository $instances,
        private readonly AuditLogRepository $auditLogs,
    ) {
    }

    public function listConversations(int $tenantId, array $filters = []): array
    {
        return $this->conversations->listByTenant($tenantId, $filters);
    }

    public function conversationDetails(int $tenantId, int $conversationId): ?array
    {
        $conversation = $this->conversations->findById($tenantId, $conversationId);
        if (!$conversation) {
            return null;
        }

        return [
            'conversation' => $conversation,
            'messages' => $this->messages->listByConversation($tenantId, $conversationId),
        ];
    }

    public function sendMessage(int $tenantId, int $actorUserId, int $conversationId, string $body): void
    {
        $conversation = $this->conversations->findById($tenantId, $conversationId);
        if (!$conversation || trim($body) === '') {
            return;
        }

        $instance = $this->instances->listByTenant($tenantId)[0] ?? null;
        $dispatch = ['status' => 'not_sent', 'reason' => 'no_connected_instance'];
        if ($instance) {
            $dispatch = $this->dispatcher->dispatch($tenantId, (int)$instance['id'], [
                'to' => $conversation['contact_phone'],
                'body' => $body,
            ]);
        }
        $this->messages->add([
            'tenant_id' => $tenantId,
            'conversation_id' => $conversationId,
            'contact_id' => (int)$conversation['contact_id'],
            'direction' => 'outbound',
            'message_type' => 'text',
            'body' => $body,
            'payload_json' => $dispatch,
            'external_message_id' => $dispatch['provider_message_id'] ?? null,
            'sent_by_user_id' => $actorUserId,
        ]);

        $this->conversations->touchLastMessage($tenantId, $conversationId);
        $this->contacts->touchInteraction($tenantId, (int)$conversation['contact_id']);
        $this->auditLogs->add($tenantId, $actorUserId, 'conversation_message_sent', 'conversation', $conversationId, ['body' => $body]);
    }

    public function addInternalNote(int $tenantId, int $actorUserId, int $conversationId, string $note): void
    {
        $conversation = $this->conversations->findById($tenantId, $conversationId);
        if (!$conversation || trim($note) === '') {
            return;
        }

        $this->messages->add([
            'tenant_id' => $tenantId,
            'conversation_id' => $conversationId,
            'contact_id' => (int)$conversation['contact_id'],
            'direction' => 'system',
            'message_type' => 'internal_note',
            'body' => $note,
            'sent_by_user_id' => $actorUserId,
        ]);

        $this->conversations->appendInternalNotes($tenantId, $conversationId, $note);
        $this->auditLogs->add($tenantId, $actorUserId, 'conversation_note_added', 'conversation', $conversationId, ['note' => $note]);
    }

    public function assignConversation(int $tenantId, int $actorUserId, int $conversationId, int $userId): void
    {
        $this->conversations->assign($tenantId, $conversationId, $userId);
        $this->auditLogs->add($tenantId, $actorUserId, 'conversation_assigned', 'conversation', $conversationId, ['assigned_user_id' => $userId]);
    }

    public function takeover(int $tenantId, int $actorUserId, int $conversationId): void
    {
        $this->conversations->takeover($tenantId, $conversationId, $actorUserId);
        $this->auditLogs->add($tenantId, $actorUserId, 'conversation_takeover', 'conversation', $conversationId, ['takeover_by_user_id' => $actorUserId]);
    }

    public function changeStatus(int $tenantId, int $actorUserId, int $conversationId, string $status): void
    {
        $allowed = ['open', 'pending', 'resolved', 'closed'];
        if (!in_array($status, $allowed, true)) {
            return;
        }

        $this->conversations->changeStatus($tenantId, $conversationId, $status);
        $this->auditLogs->add($tenantId, $actorUserId, 'conversation_status_changed', 'conversation', $conversationId, ['status' => $status]);
    }
}
