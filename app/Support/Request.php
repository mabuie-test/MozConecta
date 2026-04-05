<?php
declare(strict_types=1);

namespace App\Support;

final class Request
{
    public function method(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST' && isset($_POST['_method'])) {
            $spoofed = strtoupper((string)$_POST['_method']);
            if (in_array($spoofed, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $spoofed;
            }
        }
        return $method;
    }

    public function path(): string
    {
        return rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/') ?: '/';
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function all(): array
    {
        $raw = $this->rawBody();
        $json = json_decode($raw, true);
        return array_merge($_GET, $_POST, is_array($json) ? $json : []);
    }

    public function rawBody(): string
    {
        return file_get_contents('php://input') ?: "";
    }

    public function header(string $name, mixed $default = null): mixed
    {
        $key = "HTTP_" . strtoupper(str_replace("-", "_", $name));
        return $_SERVER[$key] ?? $default;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $_SERVER[$key] ?? $default;
    }
}
