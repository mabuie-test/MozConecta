<?php
declare(strict_types=1);

namespace App\Repositories;

final class ConversationMessageRepository extends BaseRepository
{
    public function listByConversation(int $tenantId, int $conversationId): array
    {
        $stmt = $this->pdo->prepare('SELECT cm.*, u.first_name AS user_first_name, u.last_name AS user_last_name
                                     FROM conversation_messages cm
                                     LEFT JOIN users u ON u.id = cm.sent_by_user_id
                                     WHERE cm.tenant_id=:tenant_id AND cm.conversation_id=:conversation_id
                                     ORDER BY cm.created_at ASC, cm.id ASC');
        $stmt->execute([
            'tenant_id' => $tenantId,
            'conversation_id' => $conversationId,
        ]);
        return $stmt->fetchAll();
    }

    public function add(array $data): int
    {
        $this->execute('INSERT INTO conversation_messages (tenant_id,conversation_id,contact_id,direction,message_type,body,media_url,payload_json,external_message_id,sent_by_user_id,created_at) VALUES (:tenant_id,:conversation_id,:contact_id,:direction,:message_type,:body,:media_url,:payload_json,:external_message_id,:sent_by_user_id,NOW())', [
            'tenant_id' => $data['tenant_id'],
            'conversation_id' => $data['conversation_id'],
            'contact_id' => $data['contact_id'],
            'direction' => $data['direction'],
            'message_type' => $data['message_type'],
            'body' => $data['body'],
            'media_url' => $data['media_url'] ?? null,
            'payload_json' => isset($data['payload_json']) ? json_encode($data['payload_json']) : null,
            'external_message_id' => $data['external_message_id'] ?? null,
            'sent_by_user_id' => $data['sent_by_user_id'] ?? null,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function countInboundByContact(int $tenantId, int $contactId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM conversation_messages WHERE tenant_id=:tenant_id AND contact_id=:contact_id AND direction=\'inbound\'');
        $stmt->execute(['tenant_id' => $tenantId, 'contact_id' => $contactId]);
        return (int)$stmt->fetchColumn();
    }

    public function countOutboundByContact(int $tenantId, int $contactId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM conversation_messages WHERE tenant_id=:tenant_id AND contact_id=:contact_id AND direction=\'outbound\'');
        $stmt->execute(['tenant_id' => $tenantId, 'contact_id' => $contactId]);
        return (int)$stmt->fetchColumn();
    }
}
