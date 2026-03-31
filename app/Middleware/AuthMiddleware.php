<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Support\Request;
use App\Support\Response;

final class AuthMiddleware
{
    public function handle(Request $request): bool
    {
        if (!isset($_SESSION['user_id'])) {
            Response::redirect('/login');
            return false;
        }
        return true;
    }
}
