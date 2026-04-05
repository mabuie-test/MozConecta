<?php
declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AIController;
use App\Controllers\AuthController;
use App\Controllers\BillingController;
use App\Controllers\DashboardController;
use App\Controllers\LandingController;
use App\Controllers\InboxController;
use App\Controllers\CRMController;
use App\Controllers\CampaignController;
use App\Controllers\FlowController;
use App\Controllers\InternetSalesController;
use App\Controllers\TaskController;
use App\Controllers\ProfileController;
use App\Controllers\NotificationController;
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


    // Tarefas e follow-up
    $router->get('/tasks', [TaskController::class, 'index'], $authTenantSub);
    $router->post('/tasks/create', [TaskController::class, 'create'], $authTenantSub);
    $router->post('/tasks/update', [TaskController::class, 'update'], $authTenantSub);
    $router->post('/tasks/status', [TaskController::class, 'changeStatus'], $authTenantSub);

    // Fluxos e automações
    $router->get('/flows', [FlowController::class, 'index'], $authTenantSub);
    $router->post('/flows/create', [FlowController::class, 'createFlow'], $authTenantSub);
    $router->get('/flows/show', [FlowController::class, 'show'], $authTenantSub);
    $router->post('/flows/nodes/add', [FlowController::class, 'addNode'], $authTenantSub);
    $router->post('/flows/edges/add', [FlowController::class, 'addEdge'], $authTenantSub);
    $router->post('/flows/toggle', [FlowController::class, 'toggle'], $authTenantSub);
    $router->post('/flows/run-schedules', [FlowController::class, 'runSchedules'], $authTenantSub);


    // IA por tenant
    $router->get('/ai/settings', [AIController::class, 'settings'], $authTenantSub);
    $router->post('/ai/settings/save', [AIController::class, 'saveSettings'], $authTenantSub);
    $router->post('/ai/test-hybrid', [AIController::class, 'testHybrid'], $authTenantSub);


    // Campanhas e remarketing
    $router->get('/campaigns', [CampaignController::class, 'index'], $authTenantSub);
    $router->post('/campaigns/create', [CampaignController::class, 'create'], $authTenantSub);
    $router->post('/campaigns/pause', [CampaignController::class, 'pause'], $authTenantSub);
    $router->post('/campaigns/resume', [CampaignController::class, 'resume'], $authTenantSub);
    $router->post('/campaigns/cancel', [CampaignController::class, 'cancel'], $authTenantSub);
    $router->post('/campaigns/run', [CampaignController::class, 'runBatch'], $authTenantSub);
    $router->post('/campaigns/report', [CampaignController::class, 'report'], $authTenantSub);

    // Bot venda de internet
    $router->get('/internet', [InternetSalesController::class, 'index'], $authTenantSub);
    $router->post('/internet/packages/create', [InternetSalesController::class, 'createPackage'], $authTenantSub);
    $router->post('/internet/orders/create', [InternetSalesController::class, 'createOrder'], $authTenantSub);
    $router->post('/internet/orders/status', [InternetSalesController::class, 'updateOrderStatus'], $authTenantSub);

    // Notificações
    $router->get('/notifications', [NotificationController::class, 'index'], $authTenantSub);
    $router->post('/notifications/read', [NotificationController::class, 'markRead'], $authTenantSub);

    // Webhook inbound (provider -> plataforma)
    $router->post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'inbound']);
};
