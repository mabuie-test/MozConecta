<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Integrations\Payments\BillingService;
use App\Integrations\Payments\PaymentService;
use App\Integrations\Payments\PaymentStatusPollingService;
use App\Integrations\Payments\WebhookPaymentService;
use App\Repositories\InvoiceRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\SubscriptionRepository;
use App\Support\Request;
use App\Support\Response;

final class BillingController extends BaseController
{
    public function __construct(
        private readonly BillingService $billing,
        private readonly PaymentService $payments,
        private readonly PaymentStatusPollingService $polling,
        private readonly WebhookPaymentService $webhookPayments,
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
        $planSlug = (string) $request->input('plan');
        $invoice = $this->billing->createCheckoutInvoice((int) $_SESSION['tenant_id'], $planSlug);
        $this->view('billing/checkout', ['title' => 'Checkout', 'invoice' => $invoice]);
    }

    public function checkout(Request $request): void
    {
        $result = $this->payments->checkout(
            (int) $_SESSION['tenant_id'],
            (int) $request->input('invoice_id'),
            (string) $request->input('method'),
            (string) $request->input('msisdn'),
            (string) $request->input('internal_notes')
        );

        $this->redirect('/billing/payment-status?payment_id=' . urlencode((string) $result['payment_id']));
    }

    public function paymentStatus(Request $request): void
    {
        $paymentId = (int) $request->input('payment_id');
        $payment = $this->paymentRepository->findById($paymentId);
        if (!$payment || (int) $payment['tenant_id'] !== (int) $_SESSION['tenant_id']) {
            $this->redirect('/billing/plans');
            return;
        }

        if (in_array((string) $payment['payment_status'], ['pending', 'processing'], true)) {
            $this->polling->pollOne((int) $_SESSION['tenant_id'], $payment);
            $payment = $this->paymentRepository->findById($paymentId) ?: $payment;
        }

        $invoice = $this->invoices->findById((int) $payment['invoice_id']);
        $this->view('billing/payment-status', ['title' => 'Estado de pagamento', 'payment' => $payment, 'invoice' => $invoice]);
    }

    public function paymentDetail(Request $request): void
    {
        $paymentId = (int) $request->input('payment_id');
        $payment = $this->paymentRepository->findById($paymentId);
        if (!$payment || (int) $payment['tenant_id'] !== (int) $_SESSION['tenant_id']) {
            $this->redirect('/billing/history');
            return;
        }

        $invoice = $this->invoices->findById((int) $payment['invoice_id']);
        $this->view('billing/payment-detail', ['title' => 'Detalhe do pagamento', 'payment' => $payment, 'invoice' => $invoice]);
    }

    public function manualRecheck(Request $request): void
    {
        $paymentId = (int) $request->input('payment_id');
        $payment = $this->paymentRepository->findById($paymentId);
        if ($payment && (int) $payment['tenant_id'] === (int) $_SESSION['tenant_id']) {
            $this->polling->pollOne((int) $_SESSION['tenant_id'], $payment);
        }

        $this->redirect('/billing/payment-status?payment_id=' . urlencode((string) $paymentId));
    }

    public function retry(Request $request): void
    {
        $paymentId = (int) $request->input('payment_id');
        $payment = $this->paymentRepository->findById($paymentId);
        if (!$payment || (int) $payment['tenant_id'] !== (int) $_SESSION['tenant_id']) {
            $this->redirect('/billing/history');
            return;
        }

        if (!in_array((string) $payment['payment_status'], ['failed', 'cancelled', 'canceled'], true)) {
            $this->redirect('/billing/payment-detail?payment_id=' . urlencode((string) $paymentId));
            return;
        }

        $this->redirect('/billing/checkout?plan=' . urlencode((string) ($request->input('plan') ?? 'starter')));
    }

    public function history(Request $request): void
    {
        $history = $this->billing->financialHistory((int) $_SESSION['tenant_id']);
        $this->view('billing/history', ['title' => 'Histórico financeiro', 'history' => $history]);
    }

    public function subscription(Request $request): void
    {
        $sub = $this->subscriptions->latestByTenant((int) $_SESSION['tenant_id']);
        $this->view('billing/subscription', ['title' => 'Assinatura', 'subscription' => $sub]);
    }

    public function changePlan(Request $request): void
    {
        $this->redirect('/billing/plans');
    }

    public function debitoWebhook(Request $request): void
    {
        $result = $this->webhookPayments->process($request->all(), null);
        Response::json(['ok' => true, 'result' => $result]);
    }
}
