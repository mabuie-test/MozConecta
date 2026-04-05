<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Support\Request;
use App\Support\Response;

final class ProfileMiddleware
{
    public function handle(Request $request, array $allowedRoles = []): bool
    {
        $role = (string)($_SESSION['role'] ?? '');
        if ($allowedRoles !== [] && !in_array($role, $allowedRoles, true)) {
            Response::json(['error' => 'profile_forbidden'], 403);
            return false;
        }
        return true;
    }
}
