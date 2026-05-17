<?php

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use KBuilder\Core\Application;

define('KB_ROOT', dirname(__DIR__));
define('KB_VERSION', '1.0.0');

require KB_ROOT . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(KB_ROOT);
$dotenv->load();

// Bootstrap application
$app = Application::create();
file_put_contents(__DIR__ . '/../storage/logs/request.log', date('Y-m-d H:i:s') . " - URI: " . $_SERVER['REQUEST_URI'] . " - SCRIPT: " . $_SERVER['SCRIPT_NAME'] . "\n", FILE_APPEND);

$app->run();
