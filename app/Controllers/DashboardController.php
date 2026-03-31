<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\DashboardService;
use App\Support\Request;
use App\Support\Response;

final class DashboardController
{
    public function __construct(private readonly DashboardService $dashboardService)
    {
    }

    public function index(Request $request): void
    {
        $tenantId = (int)($_SESSION['tenant_id'] ?? 0);
        Response::view('dashboard/index', ['data' => $this->dashboardService->buildTenantSummary($tenantId)]);
    }
}
