<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Services\AutomationEngineService;
use App\Services\FlowBuilderService;
use App\Support\Request;

final class FlowController extends BaseController
{
    public function __construct(
        private readonly FlowBuilderService $flows,
        private readonly AutomationEngineService $automation,
    ) {
    }

    public function index(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $this->view('flows/index', [
            'title' => 'Construtor de Fluxos',
            'flows' => $this->flows->listFlows($tenantId),
        ]);
    }

    public function createFlow(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $userId = (int)$_SESSION['user_id'];
        $flowId = $this->flows->createFlow($tenantId, $userId, [
            'name' => (string)$request->input('name', ''),
            'trigger_type' => (string)$request->input('trigger_type', 'keyword'),
            'trigger_value' => (string)$request->input('trigger_value', ''),
            'fallback_message' => (string)$request->input('fallback_message', ''),
            'allow_reentry' => (bool)$request->input('allow_reentry', false),
            'allow_remarketing' => (bool)$request->input('allow_remarketing', false),
            'is_active' => (bool)$request->input('is_active', true),
        ]);

        $this->redirect('/flows/show?id=' . $flowId);
    }

    public function show(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $flowId = (int)$request->input('id');
        $graph = $this->flows->flowGraph($tenantId, $flowId);

        $this->view('flows/show', [
            'title' => 'Editor de Fluxo',
            'graph' => $graph,
            'flowId' => $flowId,
        ]);
    }

    public function addNode(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $userId = (int)$_SESSION['user_id'];
        $flowId = (int)$request->input('flow_id');

        $config = [
            'message' => (string)$request->input('message', ''),
            'tag' => (string)$request->input('tag', ''),
            'title' => (string)$request->input('task_title', ''),
            'description' => (string)$request->input('task_description', ''),
            'stage_id' => (int)$request->input('stage_id', 0),
            'url' => (string)$request->input('url', ''),
            'minutes' => (int)$request->input('minutes', 10),
            'due_minutes' => (int)$request->input('due_minutes', 60),
        ];

        $this->flows->addNode($tenantId, $userId, $flowId, [
            'node_key' => (string)$request->input('node_key', ''),
            'type' => (string)$request->input('type', 'send_message'),
            'config_json' => $config,
            'position_x' => (int)$request->input('position_x', 0),
            'position_y' => (int)$request->input('position_y', 0),
            'is_start' => (bool)$request->input('is_start', false),
        ]);

        $this->redirect('/flows/show?id=' . $flowId);
    }

    public function addEdge(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $userId = (int)$_SESSION['user_id'];
        $flowId = (int)$request->input('flow_id');

        $this->flows->addEdge($tenantId, $userId, $flowId, [
            'from_node_id' => (int)$request->input('from_node_id'),
            'to_node_id' => (int)$request->input('to_node_id'),
            'condition_type' => (string)$request->input('condition_type', 'always'),
            'condition_value' => (string)$request->input('condition_value', ''),
            'priority' => (int)$request->input('priority', 100),
        ]);

        $this->redirect('/flows/show?id=' . $flowId);
    }

    public function toggle(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $userId = (int)$_SESSION['user_id'];
        $flowId = (int)$request->input('flow_id');
        $active = (bool)$request->input('is_active', false);
        $this->flows->setActive($tenantId, $userId, $flowId, $active);
        $this->redirect('/flows');
    }

    public function runSchedules(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $count = $this->automation->processDueSchedules($tenantId);
        $this->json(['ok' => true, 'processed' => $count]);
    }
}
