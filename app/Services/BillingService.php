<?php
declare(strict_types=1);

namespace App\Services;

final class BillingService
{
    public function canUsePaidFeature(array $subscription): bool
    {
        return in_array($subscription['status'] ?? '', ['active', 'trial_active'], true);
    }
}
