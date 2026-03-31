<?php
declare(strict_types=1);

namespace App\Core;

use App\Exceptions\ExceptionHandler;
use App\Support\Config;
use App\Support\Container;
use App\Support\Database;
use App\Support\ErrorHandler;
use App\Support\Logger;
use App\Support\Request;
use App\Support\Router;

final class Application
{
    private Container $container;
    private Config $config;
    private Router $router;

    public function __construct(private readonly string $basePath)
    {
        date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));
        session_start();

        $this->container = new Container();
        $this->config = new Config($basePath . '/config');
        $this->config->load();
        $this->router = new Router();

        $logger = new Logger($basePath . '/storage/logs/app.log');
        (new ErrorHandler(new ExceptionHandler($logger), $this->config))->register();

        $this->container->set(Config::class, $this->config);
        $this->container->set(Container::class, $this->container);
        $this->container->set(Router::class, $this->router);
        $this->container->set(Logger::class, $logger);
        $this->container->set(\PDO::class, Database::connection($this->config));
    }

    public function run(): void
    {
        (require $this->basePath . '/routes/web.php')($this->router);
        $this->router->dispatch(new Request(), $this->container);
    }
}
