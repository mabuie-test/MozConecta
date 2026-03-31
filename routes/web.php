<?php
declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\LandingController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\SubscriptionMiddleware;
use App\Middleware\TenantMiddleware;

return function (App\Support\Router $router): void {
    $router->get('/', [LandingController::class, 'index']);

    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->get('/register', [AuthController::class, 'showRegister']);
    $router->post('/register', [AuthController::class, 'register']);
    $router->post('/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);

    $protected = [AuthMiddleware::class, TenantMiddleware::class, SubscriptionMiddleware::class];
    $router->get('/dashboard', [DashboardController::class, 'index'], $protected);

    $router->get('/admin', [AdminController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]);

    // Rotas placeholder para validar suporte completo de métodos
    $router->put('/health', [LandingController::class, 'index']);
    $router->patch('/health', [LandingController::class, 'index']);
    $router->delete('/health', [LandingController::class, 'index']);
};
