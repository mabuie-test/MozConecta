<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Services\DashboardService;
use App\Services\NotificationService;
use App\Support\Request;

final class DashboardController extends BaseController
{
    public function __construct(
        private readonly DashboardService $dashboard,
        private readonly NotificationService $notifications,
    ) {
    }

    public function index(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $summary = $this->dashboard->buildTenantSummary($tenantId);

        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'subscription' => $summary['subscription'],
            'counts' => $summary['counts'],
            'usage' => $summary['usage'],
            'notifications' => $this->notifications->unread($tenantId),
            'user_name' => $_SESSION['user_name'] ?? 'Utilizador',
        ]);
    }
}
