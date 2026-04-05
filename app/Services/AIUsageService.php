<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\AIUsageLogRepository;
use App\Repositories\SubscriptionRepository;

final class AIUsageService
{
    public function __construct(
        private readonly AIUsageLogRepository $usageLogs,
        private readonly SubscriptionRepository $subscriptions,
    ) {
    }

    public function canUseAI(int $tenantId): bool
    {
        $subscription = $this->subscriptions->latestByTenant($tenantId);
        if (!$subscription) {
            return false;
        }

        $status = (string)($subscription['status_code'] ?? 'trial_expired');
        if (!in_array($status, ['trial_active', 'active', 'past_due'], true)) {
            return false;
        }

        $limit = $subscription['ai_limit'] !== null ? (int)$subscription['ai_limit'] : null;
        if ($limit === null || $limit <= 0) {
            return true;
        }

        $used = $this->usageLogs->monthUnits($tenantId, 'message');
        return $used < $limit;
    }

    public function record(int $tenantId, int $conversationId, int $contactId, string $provider, string $usageType = 'message', int $units = 1, array $metadata = []): void
    {
        $this->usageLogs->log([
            'tenant_id' => $tenantId,
            'conversation_id' => $conversationId,
            'contact_id' => $contactId,
            'provider_name' => $provider,
            'usage_type' => $usageType,
            'units_used' => $units,
            'metadata_json' => $metadata,
        ]);
    }
}
