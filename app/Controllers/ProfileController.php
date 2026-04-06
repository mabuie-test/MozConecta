<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Services\AuthService;
use App\Support\Request;

final class ProfileController extends BaseController
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function show(Request $request): void
    {
        $user = $this->authService->userById((int)$_SESSION['user_id']);
        $this->view('auth/profile', ['title' => 'Perfil', 'user' => $user]);
    }

    public function update(Request $request): void
    {
        $this->authService->updateProfile(
            (int)$_SESSION['user_id'],
            (string)$request->input('first_name'),
            (string)$request->input('last_name') ?: null,
            (string)$request->input('phone') ?: null
        );

        $user = $this->authService->userById((int)$_SESSION['user_id']);
        $this->view('auth/profile', ['title' => 'Perfil', 'user' => $user, 'success' => 'Perfil actualizado com sucesso.']);
    }

    public function changePassword(Request $request): void
    {
        $ok = $this->authService->changePassword(
            (int)$_SESSION['user_id'],
            (string)$request->input('current_password'),
            (string)$request->input('new_password')
        );

        $user = $this->authService->userById((int)$_SESSION['user_id']);
        if (!$ok) {
            $this->view('auth/profile', ['title' => 'Perfil', 'user' => $user, 'error' => 'Senha actual inválida.']);
            return;
        }

        $this->view('auth/profile', ['title' => 'Perfil', 'user' => $user, 'success' => 'Senha alterada com sucesso.']);
    }
}
