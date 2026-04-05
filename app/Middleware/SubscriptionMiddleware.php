<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Support\Request;
use App\Support\Response;

final class SubscriptionMiddleware
{
    private const ALLOWED = ['active', 'trial_active'];

    public function handle(Request $request, array $params = []): bool
    {
        $status = $_SESSION['subscription_status'] ?? null;
        if (!in_array($status, self::ALLOWED, true)) {
            Response::redirect('/billing/plans');
            return false;
        }
        return true;
    }
}
