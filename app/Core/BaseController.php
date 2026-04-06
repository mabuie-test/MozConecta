<?php
declare(strict_types=1);

namespace App\Core;

use App\Support\Request;
use App\Support\Response;

abstract class BaseController
{
    protected function request(): Request
    {
        return new Request();
    }

    protected function view(string $template, array $data = []): void
    {
        Response::view($template, $data);
    }

    protected function json(array $payload, int $status = 200): void
    {
        Response::json($payload, $status);
    }

    protected function redirect(string $path): void
    {
        Response::redirect($path);
    }
}
