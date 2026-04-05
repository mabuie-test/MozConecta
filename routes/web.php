<?php
declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\LandingController;
use App\Controllers\ProfileController;
use App\Middleware\AdminMiddleware;
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

    $core = [AuthMiddleware::class, TenantMiddleware::class, SubscriptionMiddleware::class];
    $router->get('/dashboard', [DashboardController::class, 'index'], $core);
    $router->get('/profile', [ProfileController::class, 'show'], $core);
    $router->post('/profile', [ProfileController::class, 'update'], $core);
    $router->post('/profile/change-password', [ProfileController::class, 'changePassword'], $core);

    $router->get('/admin', [AdminController::class, 'index'], [AuthMiddleware::class, TenantMiddleware::class, ProfileMiddleware::class . ':owner,admin']);

    $router->get('/billing-required', [LandingController::class, 'billingRequired']);

    // suporte de verbos adicionais
    $router->put('/profile', [ProfileController::class, 'update'], $core);
    $router->patch('/profile', [ProfileController::class, 'update'], $core);
    $router->delete('/profile', [ProfileController::class, 'show'], [AuthMiddleware::class, TenantMiddleware::class, ProfileMiddleware::class . ':owner']);
};
