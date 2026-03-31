<?php
declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\LandingController;
use App\Middleware\AuthMiddleware;

return function (App\Support\Router $router): void {
    $router->add('GET', '/', [LandingController::class, 'index']);
    $router->add('GET', '/login', [AuthController::class, 'showLogin']);
    $router->add('POST', '/login', [AuthController::class, 'login']);
    $router->add('GET', '/register', [AuthController::class, 'showRegister']);
    $router->add('POST', '/register', [AuthController::class, 'register']);
    $router->add('POST', '/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);
    $router->add('GET', '/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);
    $router->add('GET', '/admin', [AdminController::class, 'index'], [AuthMiddleware::class]);
};
