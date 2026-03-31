<?php
declare(strict_types=1);

namespace App\Support;

final class Autoloader
{
    public function __construct(private readonly string $basePath)
    {
    }

    public function register(): void
    {
        spl_autoload_register(function (string $class): void {
            if (!str_starts_with($class, 'App\\')) {
                return;
            }
            $relative = substr($class, 4);
            $file = $this->basePath . '/' . str_replace('\\', '/', $relative) . '.php';
            if (is_file($file)) {
                require_once $file;
            }
        });
    }
}
