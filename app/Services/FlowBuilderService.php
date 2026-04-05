<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\AuditLogRepository;
use App\Repositories\ChatbotEdgeRepository;
use App\Repositories\ChatbotExecutionLogRepository;
use App\Repositories\ChatbotFlowRepository;
use App\Repositories\ChatbotNodeRepository;

final class FlowBuilderService
{
    public function __construct(
        private readonly ChatbotFlowRepository $flows,
        private readonly ChatbotNodeRepository $nodes,
        private readonly ChatbotEdgeRepository $edges,
        private readonly ChatbotExecutionLogRepository $logs,
        private readonly AuditLogRepository $auditLogs,
    ) {
    }

    public function listFlows(int $tenantId): array
    {
        return $this->flows->listByTenant($tenantId);
    }

    public function createFlow(int $tenantId, int $actorUserId, array $payload): int
    {
        $flowId = $this->flows->create([
            'tenant_id' => $tenantId,
            'name' => trim((string)$payload['name']),
            'trigger_type' => (string)($payload['trigger_type'] ?? 'keyword'),
            'trigger_value' => (string)($payload['trigger_value'] ?? ''),
            'fallback_message' => (string)($payload['fallback_message'] ?? ''),
            'allow_reentry' => !empty($payload['allow_reentry']),
            'allow_remarketing' => !empty($payload['allow_remarketing']),
            'is_active' => !empty($payload['is_active']),
            'created_by_user_id' => $actorUserId,
        ]);

        $startNodeId = $this->nodes->create([
            'tenant_id' => $tenantId,
            'flow_id' => $flowId,
            'node_key' => 'start',
            'type' => 'menu',
            'config_json' => ['message' => 'Fluxo iniciado. Configure os nós e arestas.'],
            'position_x' => 100,
            'position_y' => 100,
            'is_start' => true,
        ]);

        $endNodeId = $this->nodes->create([
            'tenant_id' => $tenantId,
            'flow_id' => $flowId,
            'node_key' => 'end',
            'type' => 'end',
            'config_json' => ['message' => 'Fluxo finalizado.'],
            'position_x' => 400,
            'position_y' => 100,
            'is_start' => false,
        ]);

        $this->edges->create([
            'tenant_id' => $tenantId,
            'flow_id' => $flowId,
            'from_node_id' => $startNodeId,
            'to_node_id' => $endNodeId,
            'condition_type' => 'always',
            'condition_value' => null,
            'priority' => 1,
        ]);

        $this->auditLogs->add($tenantId, $actorUserId, 'flow_created', 'chatbot_flow', $flowId, ['name' => $payload['name']]);
        return $flowId;
    }

    public function addNode(int $tenantId, int $actorUserId, int $flowId, array $payload): int
    {
        $id = $this->nodes->create([
            'tenant_id' => $tenantId,
            'flow_id' => $flowId,
            'node_key' => trim((string)$payload['node_key']),
            'type' => (string)$payload['type'],
            'config_json' => $payload['config_json'] ?? [],
            'position_x' => (int)($payload['position_x'] ?? 0),
            'position_y' => (int)($payload['position_y'] ?? 0),
            'is_start' => !empty($payload['is_start']),
        ]);

        $this->auditLogs->add($tenantId, $actorUserId, 'flow_node_added', 'chatbot_node', $id, ['flow_id' => $flowId]);
        return $id;
    }

    public function addEdge(int $tenantId, int $actorUserId, int $flowId, array $payload): int
    {
        $id = $this->edges->create([
            'tenant_id' => $tenantId,
            'flow_id' => $flowId,
            'from_node_id' => (int)$payload['from_node_id'],
            'to_node_id' => (int)$payload['to_node_id'],
            'condition_type' => (string)($payload['condition_type'] ?? 'always'),
            'condition_value' => (string)($payload['condition_value'] ?? ''),
            'priority' => (int)($payload['priority'] ?? 100),
        ]);

        $this->auditLogs->add($tenantId, $actorUserId, 'flow_edge_added', 'chatbot_edge', $id, ['flow_id' => $flowId]);
        return $id;
    }

    public function flowGraph(int $tenantId, int $flowId): array
    {
        $flow = $this->flows->findById($tenantId, $flowId);
        return [
            'flow' => $flow,
            'nodes' => $this->nodes->byFlow($tenantId, $flowId),
            'logs' => $this->logs->latestByFlow($tenantId, $flowId),
        ];
    }

    public function setActive(int $tenantId, int $actorUserId, int $flowId, bool $active): void
    {
        $this->flows->setActive($tenantId, $flowId, $active);
        $this->auditLogs->add($tenantId, $actorUserId, 'flow_status_changed', 'chatbot_flow', $flowId, ['is_active' => $active]);
    }
}
