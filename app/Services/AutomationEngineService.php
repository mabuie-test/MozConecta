<?php
declare(strict_types=1);

namespace App\Services;

use App\Integrations\WhatsApp\MessageOutboundDispatcher;
use App\Repositories\ChatbotEdgeRepository;
use App\Repositories\ChatbotExecutionLogRepository;
use App\Repositories\ChatbotFlowRepository;
use App\Repositories\ChatbotNodeRepository;
use App\Repositories\ContactRepository;
use App\Repositories\ConversationRepository;
use App\Repositories\ScheduleRepository;
use App\Repositories\TagRepository;
use App\Repositories\TaskRepository;
use App\Repositories\WhatsAppInstanceRepository;

final class AutomationEngineService
{
    public function __construct(
        private readonly ChatbotFlowRepository $flows,
        private readonly ChatbotNodeRepository $nodes,
        private readonly ChatbotEdgeRepository $edges,
        private readonly ChatbotExecutionLogRepository $logs,
        private readonly ConversationRepository $conversations,
        private readonly ContactRepository $contacts,
        private readonly TagRepository $tags,
        private readonly TaskRepository $tasks,
        private readonly ScheduleRepository $schedules,
        private readonly MessageOutboundDispatcher $dispatcher,
        private readonly WhatsAppInstanceRepository $instances,
    ) {
    }

    public function processInbound(int $tenantId, int $conversationId, int $contactId, string $input): ?array
    {
        $flow = $this->flows->activeForInbound($tenantId, $input);
        if (!$flow) {
            return null;
        }

        $flowId = (int)$flow['id'];
        if (!(bool)$flow['allow_reentry'] && $this->flows->hasCompletedForContact($tenantId, $flowId, $contactId)) {
            $this->logs->log($tenantId, $flowId, null, $conversationId, $contactId, 'reentry_blocked', []);
            return ['status' => 'reentry_blocked'];
        }

        $start = $this->nodes->findStart($tenantId, $flowId);
        if (!$start) {
            return ['status' => 'start_node_missing'];
        }

        $current = $start;
        $guard = 0;
        while ($current && $guard < 30) {
            $guard++;
            $currentId = (int)$current['id'];
            $config = json_decode((string)($current['config_json'] ?? '{}'), true) ?: [];
            $this->logs->log($tenantId, $flowId, $currentId, $conversationId, $contactId, 'node_enter', ['type' => $current['type']]);

            if ($current['type'] === 'send_message' || $current['type'] === 'menu') {
                $message = (string)($config['message'] ?? '');
                $this->dispatchMessage($tenantId, $conversationId, $contactId, $message);
                $this->logs->log($tenantId, $flowId, $currentId, $conversationId, $contactId, 'message_sent', ['message' => $message]);
            } elseif ($current['type'] === 'apply_tag') {
                $tag = trim((string)($config['tag'] ?? ''));
                if ($tag !== '') {
                    $tagId = $this->tags->findOrCreate($tenantId, mb_strtolower($tag));
                    $existing = $this->tags->tagsForContact($contactId);
                    $ids = array_map(static fn (array $row): int => (int)$row['id'], $existing);
                    if (!in_array($tagId, $ids, true)) {
                        $ids[] = $tagId;
                    }
                    $this->tags->syncContactTags($contactId, $ids);
                }
            } elseif ($current['type'] === 'create_task') {
                $due = (int)($config['due_minutes'] ?? 60);
                $taskId = $this->tasks->create([
                    'tenant_id' => $tenantId,
                    'contact_id' => $contactId,
                    'conversation_id' => $conversationId,
                    'title' => (string)($config['title'] ?? 'Follow-up automático'),
                    'description' => (string)($config['description'] ?? 'Criado pelo motor de automação'),
                    'assigned_user_id' => isset($config['assigned_user_id']) ? (int)$config['assigned_user_id'] : null,
                    'status' => 'pending',
                    'due_at' => date('Y-m-d H:i:s', time() + ($due * 60)),
                    'metadata_json' => ['created_by' => 'automation_flow', 'flow_id' => $flowId],
                ]);
                $this->schedules->create([
                    'tenant_id' => $tenantId,
                    'task_id' => $taskId,
                    'contact_id' => $contactId,
                    'conversation_id' => $conversationId,
                    'flow_id' => $flowId,
                    'node_id' => $currentId,
                    'type' => 'follow_up',
                    'run_at' => date('Y-m-d H:i:s', time() + ($due * 60)),
                ]);
            } elseif ($current['type'] === 'move_stage') {
                $stageId = (int)($config['stage_id'] ?? 0);
                if ($stageId > 0) {
                    $this->contacts->moveStage($tenantId, $contactId, $stageId);
                }
            } elseif ($current['type'] === 'webhook') {
                $url = trim((string)($config['url'] ?? ''));
                if ($url !== '') {
                    $result = @file_get_contents($url);
                    $this->logs->log($tenantId, $flowId, $currentId, $conversationId, $contactId, 'webhook_called', ['url' => $url, 'response' => $result]);
                }
            } elseif ($current['type'] === 'handoff_human') {
                $this->conversations->changeStatus($tenantId, $conversationId, 'open');
                $this->logs->log($tenantId, $flowId, $currentId, $conversationId, $contactId, 'handoff_human', []);
                break;
            } elseif ($current['type'] === 'wait_reply') {
                $minutes = max(1, (int)($config['minutes'] ?? 10));
                $this->schedules->create([
                    'tenant_id' => $tenantId,
                    'flow_id' => $flowId,
                    'node_id' => $currentId,
                    'conversation_id' => $conversationId,
                    'contact_id' => $contactId,
                    'type' => 'flow_resume',
                    'run_at' => date('Y-m-d H:i:s', time() + ($minutes * 60)),
                    'payload_json' => ['resume_from_node_id' => $currentId],
                ]);
                $this->logs->log($tenantId, $flowId, $currentId, $conversationId, $contactId, 'wait_reply_scheduled', ['minutes' => $minutes]);
                break;
            } elseif ($current['type'] === 'end') {
                $this->logs->log($tenantId, $flowId, $currentId, $conversationId, $contactId, 'flow_completed', []);
                break;
            }

            $next = $this->resolveNextNode($tenantId, $flowId, $currentId, $input, $contactId);
            if (!$next) {
                if (!empty($flow['fallback_message'])) {
                    $this->dispatchMessage($tenantId, $conversationId, $contactId, (string)$flow['fallback_message']);
                    $this->logs->log($tenantId, $flowId, $currentId, $conversationId, $contactId, 'fallback_sent', ['message' => $flow['fallback_message']]);
                }
                break;
            }
            $current = $next;
        }

        return ['status' => 'processed', 'flow_id' => $flowId];
    }

    public function processDueSchedules(int $tenantId): int
    {
        $due = $this->schedules->due($tenantId, 200);
        $count = 0;
        foreach ($due as $row) {
            $payload = json_decode((string)($row['payload_json'] ?? '{}'), true) ?: [];
            if ($row['type'] === 'remarketing' && !empty($payload['message'])) {
                $this->dispatchMessage($tenantId, (int)$row['conversation_id'], (int)$row['contact_id'], (string)$payload['message']);
            }
            if ($row['type'] === 'flow_resume') {
                $this->logs->log($tenantId, (int)($row['flow_id'] ?? 0), (int)($row['node_id'] ?? 0), (int)($row['conversation_id'] ?? 0), (int)($row['contact_id'] ?? 0), 'flow_resumed', $payload);
            }
            $this->schedules->markProcessed((int)$row['id']);
            $count++;
        }

        return $count;
    }

    private function resolveNextNode(int $tenantId, int $flowId, int $nodeId, string $input, int $contactId): ?array
    {
        $edges = $this->edges->outgoing($flowId, $nodeId);
        foreach ($edges as $edge) {
            $conditionType = (string)$edge['condition_type'];
            $conditionValue = (string)($edge['condition_value'] ?? '');

            $ok = match ($conditionType) {
                'always' => true,
                'fallback' => true,
                'keyword', 'option' => $conditionValue !== '' && str_contains(mb_strtolower($input), mb_strtolower($conditionValue)),
                'tag' => $this->contactHasTag($contactId, $conditionValue),
                'time_window' => $this->insideTimeWindow($conditionValue),
                default => false,
            };

            if ($ok) {
                return $this->nodes->findById($tenantId, (int)$edge['to_node_id']);
            }
        }

        return null;
    }

    private function contactHasTag(int $contactId, string $tagName): bool
    {
        if ($tagName === '') {
            return false;
        }

        $tags = $this->tags->tagsForContact($contactId);
        foreach ($tags as $tag) {
            if (mb_strtolower((string)$tag['name']) === mb_strtolower($tagName)) {
                return true;
            }
        }
        return false;
    }

    private function insideTimeWindow(string $value): bool
    {
        if (!str_contains($value, '-')) {
            return false;
        }
        [$from, $to] = array_map('trim', explode('-', $value, 2));
        $now = date('H:i');
        return $now >= $from && $now <= $to;
    }

    private function dispatchMessage(int $tenantId, int $conversationId, int $contactId, string $message): void
    {
        if ($message === '') {
            return;
        }
        $conversation = $this->conversations->findById($tenantId, $conversationId);
        if (!$conversation) {
            return;
        }
        $instance = $this->instances->listByTenant($tenantId)[0] ?? null;
        if ($instance) {
            $this->dispatcher->dispatch($tenantId, (int)$instance['id'], [
                'to' => $conversation['contact_phone'],
                'body' => $message,
            ]);
        }
    }
}
