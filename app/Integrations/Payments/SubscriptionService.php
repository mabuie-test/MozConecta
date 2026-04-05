<?php
declare(strict_types=1);

namespace App\Integrations\Payments;

use App\Repositories\InvoiceRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\SubscriptionRepository;
use App\Repositories\SubscriptionStatusRepository;

final class SubscriptionService
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptions,
        private readonly SubscriptionStatusRepository $statuses,
        private readonly InvoiceRepository $invoices,
        private readonly PaymentRepository $payments,
    ) {
    }

    public function activateFromPayment(int $tenantId, int $invoiceId, int $paymentId): void
    {
        $active = $this->statuses->findByCode('active');
        $subscription = $this->subscriptions->latestByTenant($tenantId);
        if (!$active || !$subscription) {
            return;
        }

        $this->subscriptions->setStatus((int)$subscription['id'], (int)$active['id']);
        $this->invoices->markPaid($invoiceId);
        $this->payments->updateStatus($paymentId, 'success');
    }

    public function failPayment(int $invoiceId, int $paymentId, string $reason): void
    {
        $this->invoices->markFailed($invoiceId);
        $this->payments->updateStatus($paymentId, 'failed', $reason);
    }
}
