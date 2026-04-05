<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Repositories\SubscriptionRepository;
use App\Support\Request;

final class DashboardController extends BaseController
{
    public function __construct(private readonly SubscriptionRepository $subscriptions)
    {
    }

    public function index(Request $request): void
    {
        $subscription = $this->subscriptions->latestByTenant((int)$_SESSION['tenant_id']);
        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'subscription' => $subscription,
            'user_name' => $_SESSION['user_name'] ?? 'Utilizador',
        ]);
    }
}
