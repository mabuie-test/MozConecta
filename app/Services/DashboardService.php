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
        private readonly NotificationService $notifications,
    ) {
    }

    public function buildTenantSummary(int $tenantId): array
    {
        $subscription = $this->subscriptions->summaryByTenant($tenantId);
        if (($subscription['status_code'] ?? null) === 'trial_active' && !empty($subscription['trial_ends_at'])) {
            $secondsLeft = strtotime((string)$subscription['trial_ends_at']) - time();
            if ($secondsLeft > 0 && $secondsLeft <= 6 * 3600) {
                $this->notifications->push($tenantId, 'trial_near_end', 'Trial perto do fim', 'O seu trial termina em menos de 6 horas.');
            }
        }

        return [
            'counts' => $this->repository->tenantCounts($tenantId),
            'subscription' => $subscription,
            'usage' => $this->usage->currentMonth($tenantId),
        ];
    }
}
