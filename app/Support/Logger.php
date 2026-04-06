<?php
declare(strict_types=1);

namespace App\Support;

final class Logger
{
    public function __construct(private readonly string $path)
    {
    }

    public function info(string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    private function write(string $level, string $message, array $context): void
    {
        $line = sprintf("[%s] %s: %s %s\n", now(), $level, $message, json_encode($context, JSON_UNESCAPED_UNICODE));
        $dir = dirname($this->path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($this->path, $line, FILE_APPEND);
    }
}
