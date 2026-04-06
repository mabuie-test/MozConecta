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
        private readonly NotificationService $notifications,
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

        $subscription = $this->subscriptions->latestByTenant($tenantId);
        $limit = $subscription['ai_limit'] !== null ? (int)$subscription['ai_limit'] : null;
        if ($limit !== null && $limit > 0) {
            $used = $this->usageLogs->monthUnits($tenantId, 'message');
            if ($used >= (int)ceil($limit * 0.8)) {
                $this->notifications->push($tenantId, 'limit_near_end', 'Limite de IA perto do fim', 'Você usou ' . $used . ' de ' . $limit . ' mensagens IA no mês.');
            }
        }
    }
}
