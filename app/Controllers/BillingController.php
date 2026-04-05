<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Integrations\Payments\BillingService;
use App\Integrations\Payments\PaymentService;
use App\Integrations\Payments\PaymentStatusPollingService;
use App\Repositories\InvoiceRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\SubscriptionRepository;
use App\Support\Request;

final class BillingController extends BaseController
{
    public function __construct(
        private readonly BillingService $billing,
        private readonly PaymentService $payments,
        private readonly PaymentStatusPollingService $polling,
        private readonly InvoiceRepository $invoices,
        private readonly PaymentRepository $paymentRepository,
        private readonly SubscriptionRepository $subscriptions,
    ) {
    }

    public function plans(Request $request): void
    {
        $this->view('billing/plans', ['title' => 'Planos', 'plans' => $this->billing->listPlans()]);
    }

    public function checkoutPage(Request $request): void
    {
        $planSlug = (string)$request->input('plan');
        $invoice = $this->billing->createCheckoutInvoice((int)$_SESSION['tenant_id'], $planSlug);
        $this->view('billing/checkout', ['title' => 'Checkout', 'invoice' => $invoice]);
    }

    public function checkout(Request $request): void
    {
        $result = $this->payments->checkout(
            (int)$_SESSION['tenant_id'],
            (int)$request->input('invoice_id'),
            (string)$request->input('method'),
            (string)$request->input('msisdn'),
            (string)$request->input('internal_notes')
        );

        $this->redirect('/billing/payment-status?payment_id=' . urlencode((string)$result['payment_id']));
    }

    public function paymentStatus(Request $request): void
    {
        $paymentId = (int)$request->input('payment_id');
        $payment = $this->paymentRepository->findById($paymentId);
        if (!$payment || (int)$payment['tenant_id'] !== (int)$_SESSION['tenant_id']) {
            $this->redirect('/billing/plans');
            return;
        }

        if (in_array($payment['payment_status'], ['pending', 'processing'], true)) {
            $this->polling->pollPending((int)$_SESSION['tenant_id']);
            $payment = $this->paymentRepository->findById($paymentId) ?: $payment;
        }

        $invoice = $this->invoices->findById((int)$payment['invoice_id']);
        $this->view('billing/payment-status', ['title' => 'Estado de pagamento', 'payment' => $payment, 'invoice' => $invoice]);
    }

    public function history(Request $request): void
    {
        $history = $this->billing->financialHistory((int)$_SESSION['tenant_id']);
        $this->view('billing/history', ['title' => 'Histórico financeiro', 'history' => $history]);
    }

    public function subscription(Request $request): void
    {
        $sub = $this->subscriptions->latestByTenant((int)$_SESSION['tenant_id']);
        $this->view('billing/subscription', ['title' => 'Assinatura', 'subscription' => $sub]);
    }

    public function changePlan(Request $request): void
    {
        // upgrade/downgrade proporcional preparado para próxima iteração
        $this->redirect('/billing/plans');
    }
}
