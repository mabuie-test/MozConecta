<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Support\Request;
use App\Support\Response;

final class AuthController
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function showLogin(Request $request): void { Response::view('auth/login'); }
    public function showRegister(Request $request): void { Response::view('auth/register'); }

    public function register(Request $request): void
    {
        $this->authService->registerTenantOwner($request->all());
        Response::redirect('/login');
    }

    public function login(Request $request): void
    {
        $user = $this->authService->attemptLogin((string)$request->input('email'), (string)$request->input('password'));
        if (!$user) {
            Response::view('auth/login', ['error' => 'Credenciais inválidas']);
            return;
        }
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['tenant_id'] = (int)$user['tenant_id'];
        $_SESSION['role'] = $user['role'];
        Response::redirect('/dashboard');
    }

    public function logout(Request $request): void
    {
        session_destroy();
        Response::redirect('/');
    }
}
