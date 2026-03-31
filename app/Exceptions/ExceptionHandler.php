<?php
declare(strict_types=1);

namespace App\Exceptions;

use App\Support\Logger;
use Throwable;

final class ExceptionHandler
{
    public function __construct(private readonly Logger $logger)
    {
    }

    public function handle(Throwable $exception, bool $debug = false): void
    {
        $this->logger->error('Unhandled exception', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);

        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'internal_server_error',
            'message' => $debug ? $exception->getMessage() : 'Ocorreu um erro interno.',
        ]);
    }
}
