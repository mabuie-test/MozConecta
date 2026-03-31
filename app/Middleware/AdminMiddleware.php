<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Support\Request;
use App\Support\Response;

final class AdminMiddleware
{
    public function handle(Request $request): bool
    {
        if (($_SESSION['role'] ?? '') !== 'owner') {
            Response::json(['error' => 'admin_only'], 403);
            return false;
        }
        return true;
    }
}
