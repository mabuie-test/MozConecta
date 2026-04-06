<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Services\CRMService;
use App\Support\Request;

final class CRMController extends BaseController
{
    public function __construct(private readonly CRMService $crm)
    {
    }

    public function index(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $filters = [
            'search' => (string)$request->input('search', ''),
            'stage_id' => (string)$request->input('stage_id', ''),
            'assigned_user_id' => (string)$request->input('assigned_user_id', ''),
        ];

        $stages = $this->crm->ensureDefaultPipeline($tenantId);
        $contacts = $this->crm->listContacts($tenantId, $filters);

        $this->view('crm/index', [
            'title' => 'CRM de Leads',
            'contacts' => $contacts,
            'stages' => $stages,
            'filters' => $filters,
        ]);
    }

    public function store(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $actorUserId = (int)$_SESSION['user_id'];

        $this->crm->createContact($tenantId, $actorUserId, [
            'first_name' => (string)$request->input('first_name', ''),
            'last_name' => (string)$request->input('last_name', ''),
            'display_name' => (string)$request->input('display_name', ''),
            'phone' => (string)$request->input('phone', ''),
            'email' => (string)$request->input('email', ''),
            'lead_origin' => (string)$request->input('lead_origin', ''),
            'funnel_stage_id' => (string)$request->input('funnel_stage_id', ''),
            'assigned_user_id' => (string)$request->input('assigned_user_id', ''),
            'priority' => (string)$request->input('priority', 'medium'),
            'potential_value' => (string)$request->input('potential_value', ''),
            'notes' => (string)$request->input('notes', ''),
            'tags' => (string)$request->input('tags', ''),
        ]);

        $this->redirect('/crm/contacts');
    }

    public function update(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $actorUserId = (int)$_SESSION['user_id'];
        $contactId = (int)$request->input('id');

        $this->crm->updateContact($tenantId, $actorUserId, $contactId, [
            'display_name' => (string)$request->input('display_name', ''),
            'phone' => (string)$request->input('phone', ''),
            'email' => (string)$request->input('email', ''),
            'lead_origin' => (string)$request->input('lead_origin', ''),
            'funnel_stage_id' => (string)$request->input('funnel_stage_id', ''),
            'assigned_user_id' => (string)$request->input('assigned_user_id', ''),
            'priority' => (string)$request->input('priority', 'medium'),
            'potential_value' => (string)$request->input('potential_value', ''),
            'notes' => (string)$request->input('notes', ''),
            'tags' => (string)$request->input('tags', ''),
        ]);

        $this->redirect('/crm/contacts');
    }

    public function pipeline(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $board = $this->crm->pipelineBoard($tenantId);

        $this->view('crm/pipeline', [
            'title' => 'Pipeline Comercial',
            'stages' => $board['stages'],
            'contactsByStage' => $board['contacts_by_stage'],
        ]);
    }

    public function moveStage(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $actorUserId = (int)$_SESSION['user_id'];
        $contactId = (int)$request->input('contact_id');
        $stageId = (int)$request->input('stage_id');

        $this->crm->moveLeadStage($tenantId, $actorUserId, $contactId, $stageId);
        $this->redirect('/crm/pipeline');
    }
}
