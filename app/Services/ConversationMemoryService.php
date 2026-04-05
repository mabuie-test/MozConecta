<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\ConversationMessageRepository;

final class ConversationMemoryService
{
    public function __construct(private readonly ConversationMessageRepository $messages)
    {
    }

    public function recentMessages(int $tenantId, int $conversationId, int $limit = 8): array
    {
        $all = $this->messages->listByConversation($tenantId, $conversationId);
        $slice = array_slice($all, max(0, count($all) - $limit));

        $memory = [];
        foreach ($slice as $row) {
            $memory[] = [
                'role' => (($row['direction'] ?? 'inbound') === 'outbound') ? 'assistant' : 'user',
                'content' => (string)($row['body'] ?? ''),
            ];
        }

        return $memory;
    }
}
