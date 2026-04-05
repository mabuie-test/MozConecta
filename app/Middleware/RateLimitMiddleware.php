<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Support\Request;
use App\Support\Response;

final class RateLimitMiddleware
{
    public function handle(Request $request, array $params = []): bool
    {
        $max = isset($params[0]) ? max(1, (int)$params[0]) : 60;
        $window = isset($params[1]) ? max(1, (int)$params[1]) : 60;

        $ip = (string)$request->server('REMOTE_ADDR', '0.0.0.0');
        $key = 'rate_' . sha1($request->path() . '|' . $ip);
        $nowTs = time();

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'reset_at' => $nowTs + $window];
        }

        if ($nowTs > (int)$_SESSION[$key]['reset_at']) {
            $_SESSION[$key] = ['count' => 0, 'reset_at' => $nowTs + $window];
        }

        $_SESSION[$key]['count']++;

        if ((int)$_SESSION[$key]['count'] > $max) {
            Response::json(['error' => 'rate_limit_exceeded'], 429);
            return false;
        }

        return true;
    }
}
