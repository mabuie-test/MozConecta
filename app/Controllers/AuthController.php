<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Services\AuthService;
use App\Support\Request;
use Exception;

final class AuthController extends BaseController
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function showLogin(Request $request): void { $this->view('auth/login', ['title' => 'Login']); }
    public function showRegister(Request $request): void { $this->view('auth/register', ['title' => 'Registo']); }
    public function showForgotPassword(Request $request): void { $this->view('auth/forgot-password', ['title' => 'Esqueceu a senha']); }
    public function showResetPassword(Request $request): void { $this->view('auth/reset-password', ['title' => 'Redefinir senha', 'token' => (string)$request->input('token', ''), 'email' => (string)$request->input('email', '')]); }

    public function register(Request $request): void
    {
        try {
            $session = $this->authService->register($request->all());
            $this->startSession($session);
            $this->redirect('/dashboard');
        } catch (Exception $e) {
            $this->view('auth/register', ['title' => 'Registo', 'error' => $e->getMessage(), 'old' => $request->all()]);
        }
    }

    public function login(Request $request): void
    {
        try {
            $session = $this->authService->attemptLogin(
                (string)$request->input('email'),
                (string)$request->input('password'),
                (string)$request->server('REMOTE_ADDR'),
                (string)$request->server('HTTP_USER_AGENT')
            );
            $this->startSession($session);
            $this->redirect('/dashboard');
        } catch (Exception $e) {
            $this->view('auth/login', ['title' => 'Login', 'error' => $e->getMessage()]);
        }
    }

    public function requestPasswordReset(Request $request): void
    {
        $token = $this->authService->requestPasswordReset((string)$request->input('email'));
        $this->view('auth/forgot-password', [
            'title' => 'Esqueceu a senha',
            'success' => 'Se o email existir, enviámos instruções de redefinição.',
            'debug_token' => $token,
            'email' => (string)$request->input('email'),
        ]);
    }

    public function resetPassword(Request $request): void
    {
        $ok = $this->authService->resetPassword(
            (string)$request->input('email'),
            (string)$request->input('token'),
            (string)$request->input('password')
        );

        if (!$ok) {
            $this->view('auth/reset-password', [
                'title' => 'Redefinir senha',
                'error' => 'Token inválido ou expirado.',
                'token' => (string)$request->input('token'),
                'email' => (string)$request->input('email'),
            ]);
            return;
        }

        $this->redirect('/login');
    }

    public function logout(Request $request): void
    {
        $_SESSION = [];
        session_destroy();
        $this->redirect('/');
    }

    private function startSession(array $session): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $session['user_id'];
        $_SESSION['tenant_id'] = $session['tenant_id'];
        $_SESSION['role'] = $session['role'];
        $_SESSION['subscription_status'] = $session['subscription_status'];
        $_SESSION['user_name'] = $session['name'] ?? null;
        $_SESSION['email_verified'] = $session['email_verified'] ?? false;
    }
}
