<?php
declare(strict_types=1);

namespace App\Support;

final class Response
{
    public static function view(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require base_path('app/Views/' . $template . '.php');
    }

    public static function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    public static function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}
