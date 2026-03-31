<?php
declare(strict_types=1);

namespace App\Support;

use ReflectionClass;

final class Container
{
    private array $instances = [];

    public function set(string $id, object $instance): void { $this->instances[$id] = $instance; }

    public function get(string $id): object
    {
        if (isset($this->instances[$id])) return $this->instances[$id];
        $reflection = new ReflectionClass($id);
        $ctor = $reflection->getConstructor();
        if (!$ctor) return $this->instances[$id] = new $id();
        $deps = [];
        foreach ($ctor->getParameters() as $param) {
            $deps[] = $this->get($param->getType()?->getName() ?? '');
        }
        return $this->instances[$id] = $reflection->newInstanceArgs($deps);
    }
}
