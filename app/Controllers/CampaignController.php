<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Services\CampaignService;
use App\Support\Request;

final class CampaignController extends BaseController
{
    public function __construct(private readonly CampaignService $campaigns)
    {
    }

    public function index(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $this->view('campaigns/index', [
            'title' => 'Campanhas e Remarketing',
            'campaigns' => $this->campaigns->listCampaigns($tenantId),
        ]);
    }

    public function create(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $this->campaigns->createCampaign($tenantId, [
            'name' => (string)$request->input('name', ''),
            'type' => (string)$request->input('type', 'broadcast'),
            'message_template' => (string)$request->input('message_template', ''),
            'segment_type' => (string)$request->input('segment_type', 'all'),
            'segment_value' => (string)$request->input('segment_value', ''),
            'batch_size' => (int)$request->input('batch_size', 50),
            'scheduled_at' => (string)$request->input('scheduled_at', ''),
        ]);

        $this->redirect('/campaigns');
    }

    public function pause(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $this->campaigns->pause($tenantId, (int)$request->input('id'));
        $this->redirect('/campaigns');
    }

    public function resume(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $this->campaigns->resume($tenantId, (int)$request->input('id'));
        $this->redirect('/campaigns');
    }

    public function cancel(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $this->campaigns->cancel($tenantId, (int)$request->input('id'));
        $this->redirect('/campaigns');
    }

    public function runBatch(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $result = $this->campaigns->runBatch($tenantId, (int)$request->input('id'));
        $this->json(['ok' => true, 'result' => $result]);
    }

    public function report(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $report = $this->campaigns->report($tenantId, (int)$request->input('id'));
        $this->json(['ok' => true, 'report' => $report]);
    }
}
