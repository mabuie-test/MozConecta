<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Support\Request;

final class AuthController extends BaseController
{
    public function showLogin(Request $request): void { $this->view('auth/login', ['title' => 'Login']); }
    public function showRegister(Request $request): void { $this->view('auth/register', ['title' => 'Registo']); }

    public function login(Request $request): void
    {
        // Fase 1: apenas stub de autenticação
        $_SESSION['user_id'] = 1;
        $_SESSION['tenant_id'] = 1;
        $_SESSION['role'] = 'owner';
        $_SESSION['subscription_status'] = 'trial_active';
        $this->redirect('/dashboard');
    }

    public function register(Request $request): void
    {
        $this->redirect('/login');
    }

    public function logout(Request $request): void
    {
        session_destroy();
        $this->redirect('/');
    }
}
