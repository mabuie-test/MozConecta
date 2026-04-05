<?php
declare(strict_types=1);

namespace App\Integrations\Payments;

use App\Repositories\InvoiceRepository;
use App\Repositories\PlanRepository;
use App\Repositories\SubscriptionRepository;

final class BillingService
{
    public function __construct(
        private readonly InvoiceRepository $invoices,
        private readonly PlanRepository $plans,
        private readonly SubscriptionRepository $subscriptions,
    ) {
    }

    public function createCheckoutInvoice(int $tenantId, string $planSlug): array
    {
        $plan = $this->plans->findBySlug($planSlug);
        if (!$plan) {
            throw new \RuntimeException('Plano inválido.');
        }

        $subscription = $this->subscriptions->latestByTenant($tenantId);
        if (!$subscription) {
            throw new \RuntimeException('Subscrição não encontrada para tenant.');
        }

        $invoiceId = $this->invoices->create(
            $tenantId,
            (int)$subscription['id'],
            (int)$plan['id'],
            (float)$plan['price_mt'],
            (string)$plan['currency']
        );

        return $this->invoices->findById($invoiceId) ?? [];
    }

    public function listPlans(): array
    {
        return $this->plans->listActive();
    }

    public function financialHistory(int $tenantId): array
    {
        return $this->invoices->listByTenant($tenantId);
    }
}
