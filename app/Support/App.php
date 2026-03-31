<?php
declare(strict_types=1);

namespace App\Support;

final class App
{
    private Container $container;
    private Config $config;
    private Router $router;

    public function __construct(private readonly string $basePath)
    {
        session_start();
        $this->container = new Container();
        $this->config = new Config($basePath . '/config');
        $this->config->load();
        $this->router = new Router();

        $this->container->set(Config::class, $this->config);
        $this->container->set(Container::class, $this->container);
        $this->container->set(Router::class, $this->router);

        $pdo = Database::connection($this->config);
        $this->container->set(\PDO::class, $pdo);
    }

    public function run(): void
    {
        (require $this->basePath . '/routes/web.php')($this->router);
        $this->router->dispatch(new Request(), $this->container);
    }
}
