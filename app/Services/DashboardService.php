<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\DashboardRepository;
use App\Repositories\SubscriptionRepository;

final class DashboardService
{
    public function __construct(
        private readonly DashboardRepository $repository,
        private readonly SubscriptionRepository $subscriptions,
        private readonly UsageService $usage,
    ) {
    }

    public function buildTenantSummary(int $tenantId): array
    {
        return [
            'counts' => $this->repository->tenantCounts($tenantId),
            'subscription' => $this->subscriptions->summaryByTenant($tenantId),
            'usage' => $this->usage->currentMonth($tenantId),
        ];
    }
}
