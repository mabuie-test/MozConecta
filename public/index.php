<?php
declare(strict_types=1);

use App\Support\App;

require_once __DIR__ . '/../app/Support/helpers.php';
require_once __DIR__ . '/../app/Support/Autoloader.php';

$autoloader = new App\Support\Autoloader(__DIR__ . '/../app');
$autoloader->register();

$app = new App(__DIR__ . '/..');
$app->run();
