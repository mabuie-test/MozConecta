<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Services\NotificationService;
use App\Support\Request;

final class NotificationController extends BaseController
{
    public function __construct(private readonly NotificationService $notifications)
    {
    }

    public function index(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $this->view('notifications/index', [
            'title' => 'Notificações internas',
            'notifications' => $this->notifications->unread($tenantId),
        ]);
    }

    public function markRead(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $this->notifications->markRead($tenantId, (int)$request->input('id'));
        $this->redirect('/notifications');
    }
}
