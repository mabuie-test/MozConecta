<?php
declare(strict_types=1);

namespace App\Support;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler, array $middleware = []): void { $this->add('GET', $path, $handler, $middleware); }
    public function post(string $path, array $handler, array $middleware = []): void { $this->add('POST', $path, $handler, $middleware); }
    public function put(string $path, array $handler, array $middleware = []): void { $this->add('PUT', $path, $handler, $middleware); }
    public function patch(string $path, array $handler, array $middleware = []): void { $this->add('PATCH', $path, $handler, $middleware); }
    public function delete(string $path, array $handler, array $middleware = []): void { $this->add('DELETE', $path, $handler, $middleware); }

    public function add(string $method, string $path, array $handler, array $middleware = []): void
    {
        $method = strtoupper($method);
        $path = rtrim($path, '/') ?: '/';
        $this->routes[$method][$path] = ['handler' => $handler, 'middleware' => $middleware];
    }

    public function dispatch(Request $request, Container $container): void
    {
        $route = $this->routes[$request->method()][$request->path()] ?? null;
        if (!$route) {
            Response::view('errors/404', ['title' => '404'], 404);
            return;
        }

        foreach ($route['middleware'] as $entry) {
            $params = [];
            $middlewareClass = $entry;
            if (is_string($entry) && str_contains($entry, ':')) {
                [$middlewareClass, $rawParams] = explode(':', $entry, 2);
                $params = array_filter(array_map('trim', explode(',', $rawParams)));
            }

            $middleware = $container->get($middlewareClass);
            if (method_exists($middleware, 'handle') && $middleware->handle($request, $params) === false) {
                return;
            }
        }

        [$controllerClass, $method] = $route['handler'];
        $controller = $container->get($controllerClass);
        $controller->{$method}($request);
    }
}
