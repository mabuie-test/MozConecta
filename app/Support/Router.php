<?php
declare(strict_types=1);

namespace App\Support;

final class Router
{
    private array $routes = [];

    public function add(string $method, string $path, array $handler, array $middleware = []): void
    {
        $this->routes[strtoupper($method)][$path] = ['handler' => $handler, 'middleware' => $middleware];
    }

    public function dispatch(Request $request, Container $container): void
    {
        $route = $this->routes[$request->method()][$request->path()] ?? null;
        if (!$route) {
            http_response_code(404);
            echo '404';
            return;
        }
        foreach ($route['middleware'] as $middlewareClass) {
            $middleware = $container->get($middlewareClass);
            if ($middleware->handle($request) === false) {
                return;
            }
        }
        [$class, $method] = $route['handler'];
        $controller = $container->get($class);
        $controller->{$method}($request);
    }
}
