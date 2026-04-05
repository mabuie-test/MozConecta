<?php
declare(strict_types=1);

namespace App\Support;

final class Config
{
    private array $items = [];

    public function __construct(private readonly string $configPath)
    {
    }

    public function load(): void
    {
        foreach (glob($this->configPath . '/*.php') as $file) {
            $key = basename($file, '.php');
            $this->items[$key] = require $file;
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = $this->items;
        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }
}
