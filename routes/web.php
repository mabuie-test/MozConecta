<?php
declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\BillingController;
use App\Controllers\DashboardController;
use App\Controllers\LandingController;
use App\Controllers\InboxController;
use App\Controllers\CRMController;
use App\Controllers\ProfileController;
use App\Controllers\WhatsAppInstanceController;
use App\Controllers\WhatsAppWebhookController;
use App\Middleware\AuthMiddleware;
use App\Middleware\ProfileMiddleware;
use App\Middleware\SubscriptionMiddleware;
use App\Middleware\TenantMiddleware;

return function (App\Support\Router $router): void {
    $router->get('/', [LandingController::class, 'index']);

    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->get('/register', [AuthController::class, 'showRegister']);
    $router->post('/register', [AuthController::class, 'register']);
    $router->post('/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);

    $router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
    $router->post('/forgot-password', [AuthController::class, 'requestPasswordReset']);
    $router->get('/reset-password', [AuthController::class, 'showResetPassword']);
    $router->post('/reset-password', [AuthController::class, 'resetPassword']);

    $authTenant = [AuthMiddleware::class, TenantMiddleware::class];
    $authTenantSub = [AuthMiddleware::class, TenantMiddleware::class, SubscriptionMiddleware::class];

    $router->get('/dashboard', [DashboardController::class, 'index'], $authTenantSub);
    $router->get('/profile', [ProfileController::class, 'show'], $authTenantSub);
    $router->post('/profile', [ProfileController::class, 'update'], $authTenantSub);
    $router->post('/profile/change-password', [ProfileController::class, 'changePassword'], $authTenantSub);

    $router->get('/admin', [AdminController::class, 'index'], [AuthMiddleware::class, TenantMiddleware::class, ProfileMiddleware::class . ':owner,admin']);

    // Billing / checkout
    $router->get('/billing/plans', [BillingController::class, 'plans'], $authTenant);
    $router->get('/billing/checkout', [BillingController::class, 'checkoutPage'], $authTenant);
    $router->post('/billing/checkout', [BillingController::class, 'checkout'], $authTenant);
    $router->get('/billing/payment-status', [BillingController::class, 'paymentStatus'], $authTenant);
    $router->get('/billing/history', [BillingController::class, 'history'], $authTenant);
    $router->get('/billing/subscription', [BillingController::class, 'subscription'], $authTenant);
    $router->post('/billing/change-plan', [BillingController::class, 'changePlan'], $authTenant);

    // WhatsApp instances
    $router->get('/whatsapp/instances', [WhatsAppInstanceController::class, 'index'], $authTenantSub);
    $router->post('/whatsapp/instances/create', [WhatsAppInstanceController::class, 'create'], $authTenantSub);
    $router->post('/whatsapp/instances/edit', [WhatsAppInstanceController::class, 'edit'], $authTenantSub);
    $router->post('/whatsapp/instances/pair', [WhatsAppInstanceController::class, 'startPairing'], $authTenantSub);
    $router->post('/whatsapp/instances/reconnect', [WhatsAppInstanceController::class, 'reconnect'], $authTenantSub);
    $router->post('/whatsapp/instances/disconnect', [WhatsAppInstanceController::class, 'disconnect'], $authTenantSub);
    $router->post('/whatsapp/instances/delete', [WhatsAppInstanceController::class, 'delete'], $authTenantSub);
    $router->post('/whatsapp/instances/sync', [WhatsAppInstanceController::class, 'sync'], $authTenantSub);
    $router->get('/whatsapp/instances/show', [WhatsAppInstanceController::class, 'show'], $authTenantSub);


    // Inbox e CRM
    $router->get('/inbox', [InboxController::class, 'index'], $authTenantSub);
    $router->get('/inbox/show', [InboxController::class, 'show'], $authTenantSub);
    $router->post('/inbox/send', [InboxController::class, 'send'], $authTenantSub);
    $router->post('/inbox/note', [InboxController::class, 'addNote'], $authTenantSub);
    $router->post('/inbox/assign', [InboxController::class, 'assign'], $authTenantSub);
    $router->post('/inbox/takeover', [InboxController::class, 'takeover'], $authTenantSub);
    $router->post('/inbox/status', [InboxController::class, 'changeStatus'], $authTenantSub);

    $router->get('/crm/contacts', [CRMController::class, 'index'], $authTenantSub);
    $router->post('/crm/contacts/store', [CRMController::class, 'store'], $authTenantSub);
    $router->post('/crm/contacts/update', [CRMController::class, 'update'], $authTenantSub);
    $router->get('/crm/pipeline', [CRMController::class, 'pipeline'], $authTenantSub);
    $router->post('/crm/pipeline/move', [CRMController::class, 'moveStage'], $authTenantSub);

    // Webhook inbound (provider -> plataforma)
    $router->post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'inbound']);
};
