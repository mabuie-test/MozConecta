<?php
declare(strict_types=1);

namespace App\Support;

use App\Exceptions\ExceptionHandler;
use Throwable;

final class ErrorHandler
{
    public function __construct(private readonly ExceptionHandler $exceptionHandler, private readonly Config $config)
    {
    }

    public function register(): void
    {
        set_exception_handler(fn(Throwable $e) => $this->exceptionHandler->handle($e, (bool)$this->config->get('app.debug', false)));
        set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
    }
}
