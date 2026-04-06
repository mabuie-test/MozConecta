<?php
declare(strict_types=1);

use App\Core\Application;

$basePath = dirname(__DIR__);

if (is_file($basePath . '/vendor/autoload.php')) {
    require_once $basePath . '/vendor/autoload.php';
} else {
    require_once $basePath . '/app/Support/helpers.php';
    require_once $basePath . '/app/Support/Autoloader.php';
    $fallbackLoader = new App\Support\Autoloader($basePath . '/app');
    $fallbackLoader->register();
}

return new Application($basePath);
