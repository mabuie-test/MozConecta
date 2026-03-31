<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Support\Request;
use App\Support\Response;

final class TenantMiddleware
{
    public function handle(Request $request): bool
    {
        if (!isset($_SESSION['tenant_id'])) {
            Response::json(['error' => 'tenant_context_required'], 403);
            return false;
        }
        return true;
    }
}
