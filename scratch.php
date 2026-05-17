<?php
require 'vendor/autoload.php';

use Slim\Factory\AppFactory;
use KBuilder\Core\Application;

define('KB_ROOT', __DIR__);

$dotenv = Dotenv\Dotenv::createImmutable(KB_ROOT);
$dotenv->load();

$app = Application::create();
$container = $app->getContainer();
$registry = $container->get(KBuilder\Core\Component\ComponentRegistry::class);

$list = $registry->toBuilderList();
echo json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
